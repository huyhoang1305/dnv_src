{{-- Auto Image Compressor for Filament RichEditor & Curator --}}
<script>
(function () {
    if (window._filamentImageCompressorInitialized) return;
    window._filamentImageCompressorInitialized = true;

    const MAX_SIZE_BYTES = 950 * 1024; // 950 KB (strictly under 1MB)
    const MAX_DIMENSION = 1920; // 1920px max width/height

    /**
     * Compress an image file to under 1MB using HTML5 Canvas
     */
    function compressImage(file, maxBytes = MAX_SIZE_BYTES) {
        return new Promise((resolve) => {
            if (!file || !(file instanceof File)) {
                return resolve(file);
            }

            // Skip non-images or formats not suited for canvas recompression
            if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || file.type === 'image/gif') {
                return resolve(file);
            }

            // If already below target size, no need to compress
            if (file.size <= maxBytes) {
                return resolve(file);
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.naturalWidth || img.width;
                    let height = img.naturalHeight || img.height;

                    if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                        if (width > height) {
                            height = Math.round((height * MAX_DIMENSION) / width);
                            width = MAX_DIMENSION;
                        } else {
                            width = Math.round((width * MAX_DIMENSION) / height);
                            height = MAX_DIMENSION;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    function attemptCompress(w, h, quality) {
                        canvas.width = w;
                        canvas.height = h;
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';
                        ctx.clearRect(0, 0, w, h);
                        ctx.drawImage(img, 0, 0, w, h);

                        const targetMime = 'image/webp';
                        canvas.toBlob((blob) => {
                            if (!blob) {
                                return resolve(file);
                            }

                            if (blob.size <= maxBytes || (quality <= 0.35 && w <= 800)) {
                                const baseName = file.name.replace(/\.[^/.]+$/, '');
                                const compressedFile = new File([blob], `${baseName}.webp`, {
                                    type: targetMime,
                                    lastModified: Date.now(),
                                });
                                console.log(`[AutoCompress] "${file.name}" (${(file.size / 1024 / 1024).toFixed(2)} MB) -> "${compressedFile.name}" (${(blob.size / 1024).toFixed(1)} KB)`);
                                resolve(compressedFile);
                            } else {
                                if (quality > 0.4) {
                                    attemptCompress(w, h, quality - 0.15);
                                } else {
                                    const newW = Math.round(w * 0.8);
                                    const newH = Math.round(h * 0.8);
                                    attemptCompress(newW, newH, 0.72);
                                }
                            }
                        }, targetMime, quality);
                    }

                    attemptCompress(width, height, 0.82);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    /**
     * Generate unique file key matching Filament's format
     */
    function generateFileKey() {
        return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
            (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16)
        );
    }

    /**
     * Process and upload image files to RichEditor TipTap
     */
    async function processAndUploadImagesToRichEditor({ files, editorWrapper, clientX, clientY }) {
        const alpineEl = editorWrapper.querySelector('[x-data*="richEditorFormComponent"]') || editorWrapper;
        const alpine = window.Alpine?.$data(alpineEl);
        if (!alpine) return;

        const editor = alpine.getEditor ? alpine.getEditor() : null;
        if (!editor) return;

        const editorView = editor.view;
        const xData = alpineEl.getAttribute('x-data') || '';

        const keyMatch = xData.match(/key:\s*['"]([^'"]+)['"]/);
        const key = keyMatch ? keyMatch[1] : (alpine.key || null);

        const statePathMatch = xData.match(/statePath:\s*['"]([^'"]+)['"]/);
        const statePath = statePathMatch ? statePathMatch[1] : (alpine.statePath || null);

        const wireEl = editorWrapper.closest('[wire\\:id]');
        const livewireId = wireEl?.getAttribute('wire:id');
        const wire = alpine.$wire || (livewireId && window.Livewire ? window.Livewire.find(livewireId) : null);

        if (!key || !statePath || !wire) {
            console.warn('[RichEditor AutoCompress] Missing key, statePath, or wire instance', { key, statePath, wire });
            return;
        }

        // Determine insertion position
        let insertPos = editor.state.selection.anchor;
        if (clientX !== undefined && clientY !== undefined && editorView) {
            const coords = editorView.posAtCoords({ left: clientX, top: clientY });
            if (coords && coords.pos) {
                insertPos = coords.pos;
            }
        }

        // Show uploading state in UI
        alpine.isUploadingFile = true;
        alpine.fileValidationMessage = null;
        editor.setEditable(false);

        editorView.dom.dispatchEvent(
            new CustomEvent('rich-editor-uploading-file', {
                bubbles: true,
                detail: { key, livewireId },
            })
        );

        for (let i = 0; i < files.length; i++) {
            const rawFile = files[i];
            const compressedFile = await compressImage(rawFile);
            const fileKey = generateFileKey();

            await new Promise((resolveUpload) => {
                wire.upload(
                    `componentFileAttachments.${statePath}.${fileKey}`,
                    compressedFile,
                    () => {
                        // Success callback: request temporary URL from backend
                        const getUrlPromise = window.Livewire?.fireAction
                            ? window.Livewire.fireAction(
                                  wire.__instance,
                                  'callSchemaComponentMethod',
                                  [key, 'getUploadedFileAttachmentTemporaryUrl', { attachment: fileKey }],
                                  { async: true }
                              )
                            : (wire.callSchemaComponentMethod
                                ? wire.callSchemaComponentMethod(key, 'getUploadedFileAttachmentTemporaryUrl', { attachment: fileKey })
                                : Promise.resolve(null));

                        Promise.resolve(getUrlPromise)
                            .then((url) => {
                                if (url) {
                                    editor
                                        .chain()
                                        .insertContentAt(insertPos, {
                                            type: 'image',
                                            attrs: {
                                                id: fileKey,
                                                src: url,
                                            },
                                        })
                                        .run();
                                    // Move insertPos forward for subsequent files
                                    insertPos += 1;
                                }
                                resolveUpload();
                            })
                            .catch((err) => {
                                console.error('[RichEditor AutoCompress] Failed to retrieve temp URL:', err);
                                resolveUpload();
                            });
                    },
                    (err) => {
                        console.error('[RichEditor AutoCompress] Upload failed:', err);
                        resolveUpload();
                    }
                );
            });
        }

        // Restore editor state
        editor.setEditable(true);
        alpine.isUploadingFile = false;

        editorView.dom.dispatchEvent(
            new CustomEvent('rich-editor-uploaded-file', {
                bubbles: true,
                detail: { key, livewireId },
            })
        );
    }

    /**
     * Intercept Drag & Drop on RichEditor in CAPTURE phase
     */
    document.addEventListener(
        'drop',
        async function (e) {
            const editorWrapper = e.target.closest('.fi-fo-rich-editor');
            if (!editorWrapper) return;

            const dt = e.dataTransfer;
            if (!dt || !dt.files || !dt.files.length) return;

            const files = Array.from(dt.files);
            const imageFiles = files.filter((f) => f.type.startsWith('image/'));
            if (!imageFiles.length) return;

            // Stop ProseMirror handleDrop from running and triggering validation error
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            await processAndUploadImagesToRichEditor({
                files: imageFiles,
                editorWrapper,
                clientX: e.clientX,
                clientY: e.clientY,
            });
        },
        true // CAPTURE phase
    );

    /**
     * Intercept Paste on RichEditor in CAPTURE phase
     */
    document.addEventListener(
        'paste',
        async function (e) {
            const editorWrapper = e.target.closest('.fi-fo-rich-editor');
            if (!editorWrapper) return;

            const cb = e.clipboardData;
            if (!cb || !cb.files || !cb.files.length) return;

            const files = Array.from(cb.files);
            const imageFiles = files.filter((f) => f.type.startsWith('image/'));
            if (!imageFiles.length) return;

            // Stop ProseMirror handlePaste from running and triggering validation error
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            await processAndUploadImagesToRichEditor({
                files: imageFiles,
                editorWrapper,
            });
        },
        true // CAPTURE phase
    );

    /**
     * FilePond & Curator Auto-Compression Hook
     */
    function applyFilePondHook() {
        if (!window.FilePond || window.FilePond._curatorAutoCompressApplied) return;
        window.FilePond._curatorAutoCompressApplied = true;

        const origCreate = window.FilePond.create;
        window.FilePond.create = function (element, options = {}) {
            const pond = origCreate.call(this, element, options);
            const origAddFile = pond.addFile;
            pond.addFile = async function (source, fileOptions) {
                if (source instanceof File && source.type.startsWith('image/') && source.size > MAX_SIZE_BYTES) {
                    source = await compressImage(source, MAX_SIZE_BYTES);
                }
                return origAddFile.call(this, source, fileOptions);
            };
            const origAddFiles = pond.addFiles;
            pond.addFiles = async function (sources, fileOptions) {
                if (Array.isArray(sources)) {
                    sources = await Promise.all(
                        sources.map(async (s) => {
                            if (s instanceof File && s.type.startsWith('image/') && s.size > MAX_SIZE_BYTES) {
                                return await compressImage(s, MAX_SIZE_BYTES);
                            }
                            return s;
                        })
                    );
                }
                return origAddFiles.call(this, sources, fileOptions);
            };
            return pond;
        };
    }

    /**
     * Intercept file input change in Curator / FilePond
     */
    document.addEventListener(
        'change',
        async function (e) {
            if (e.target && e.target.matches('input[type="file"]') && (e.target.closest('.filepond--root') || e.target.closest('.curator-panel'))) {
                const files = e.target.files;
                if (!files || !files.length || e.target._isAutoCompressing) return;

                let needsCompression = false;
                for (let i = 0; i < files.length; i++) {
                    if (files[i].type.startsWith('image/') && files[i].size > MAX_SIZE_BYTES) {
                        needsCompression = true;
                        break;
                    }
                }

                if (needsCompression) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    e.target._isAutoCompressing = true;
                    const dt = new DataTransfer();
                    for (let i = 0; i < files.length; i++) {
                        const f = files[i];
                        if (f.type.startsWith('image/') && f.size > MAX_SIZE_BYTES) {
                            const compressed = await compressImage(f, MAX_SIZE_BYTES);
                            dt.items.add(compressed);
                        } else {
                            dt.items.add(f);
                        }
                    }
                    e.target.files = dt.files;
                    e.target._isAutoCompressing = false;
                    e.target.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        },
        true
    );

    setInterval(applyFilePondHook, 200);
    document.addEventListener('DOMContentLoaded', applyFilePondHook);
    document.addEventListener('livewire:navigated', applyFilePondHook);
})();
</script>
