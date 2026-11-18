<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%;"
            x-data="accountStorageCompressor()">

            <!-- Left: Storage Info -->
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="display: flex; flex-direction: column;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; letter-spacing: -0.025em; margin: 0; line-height: 1.2; text-transform: uppercase;"
                        class="text-gray-950 dark:text-white">
                        STORAGE: <span style="color: #f59e0b;" class="font-mono font-black"
                            x-text="totalSize">{{ $storageStats['total_size'] ?? '0 MB' }}</span>
                    </h2>
                    <div style="font-size: 0.8rem; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                        <template x-if="oversizedCount > 0">
                            <span
                                style="color: #f59e0b; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <span
                                    style="width: 7px; height: 7px; border-radius: 9999px; background: #f59e0b; display: inline-block;"
                                    class="animate-pulse"></span>
                                <span x-text="`${oversizedCount} ảnh lớn hơn 1MB`"></span>
                            </span>
                        </template>
                        <template x-if="oversizedCount === 0">
                            <span
                                style="color: #10b981; font-weight: 500; display: inline-flex; align-items: center; gap: 5px;">
                                <span
                                    style="width: 7px; height: 7px; border-radius: 9999px; background: #10b981; display: inline-block;"></span>
                                Đã tối ưu (tất cả ảnh &lt; 1MB)
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons in 1 row -->
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-shrink: 0;">
                <!-- Nén Storage Button -->
                <button type="button" x-on:click="startCompression()" x-bind:disabled="isCompressing"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 15px; font-size: 0.8rem; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s; background-color: #d97706; color: #ffffff; white-space: nowrap;"
                    onmouseover="if(!this.disabled) this.style.backgroundColor='#b45309'"
                    onmouseout="if(!this.disabled) this.style.backgroundColor='#d97706'">
                    <svg x-show="isCompressing"
                        style="width: 14px; height: 14px; animation: spin 1s linear infinite; display: none;"
                        fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <svg x-show="!isCompressing" style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z" />
                    </svg>
                    <span x-text="buttonText">Nén Storage</span>
                </button>

                <!-- Xem Website Button -->
                <x-filament::button tag="a" href="{{ url('/') }}" target="_blank" color="gray"
                    size="sm" :icon="\Filament\Support\Icons\Heroicon::ArrowTopRightOnSquare">
                    XEM WEBSITE
                </x-filament::button>
            </div>
        </div>

        <script>
            function accountStorageCompressor() {
                return {
                    isCompressing: false,
                    buttonText: 'Nén Storage',
                    oversizedCount: {{ (int) ($storageStats['oversized_count'] ?? 0) }},
                    totalSize: '{{ $storageStats['total_size'] ?? '0 MB' }}',

                    async startCompression() {
                        if (this.isCompressing) return;

                        this.isCompressing = true;
                        this.buttonText = 'Đang quét...';

                        try {
                            const oversizedFiles = await this.$wire.getOversizedFiles();

                            if (!oversizedFiles || oversizedFiles.length === 0) {
                                this.buttonText = 'Đã tối ưu!';
                                this.oversizedCount = 0;
                                await this.$wire.notifyAlreadyOptimized();
                                setTimeout(() => {
                                    this.buttonText = 'Nén Storage';
                                    this.isCompressing = false;
                                }, 2000);
                                return;
                            }

                            let processedCount = 0;
                            let totalSavedBytes = 0;

                            for (let i = 0; i < oversizedFiles.length; i++) {
                                const file = oversizedFiles[i];
                                this.buttonText = `${i + 1}/${oversizedFiles.length}...`;

                                try {
                                    // Fetch file blob with cache-buster
                                    const response = await fetch(file.url + '?t=' + Date.now());
                                    if (!response.ok) continue;

                                    const originalBlob = await response.blob();

                                    // Compress in browser via Canvas to under 950KB (strict < 1MB)
                                    const compressedBlob = await this.compressImageBlob(originalBlob, file.ext, 950000);

                                    // Convert to Base64
                                    const base64Data = await this.blobToBase64(compressedBlob);

                                    // Save back to storage via backend
                                    const res = await this.$wire.saveCompressedImage(file.path, base64Data);

                                    if (res && res.success) {
                                        processedCount++;
                                        totalSavedBytes += (res.saved || 0);
                                    }
                                } catch (fileErr) {
                                    console.error('Lỗi nén file:', file.path, fileErr);
                                }
                            }

                            // Notify completion & update stats
                            await this.$wire.notifyCompressionComplete(processedCount, totalSavedBytes);

                            // Refresh component stats
                            const stats = await this.$wire.getStorageStats();
                            if (stats) {
                                this.oversizedCount = stats.oversized_count;
                                this.totalSize = stats.total_size;
                            }

                            this.buttonText = 'Đã xong!';
                            setTimeout(() => {
                                this.buttonText = 'Nén Storage';
                                this.isCompressing = false;
                            }, 2500);

                        } catch (err) {
                            console.error('Lỗi quá trình nén storage:', err);
                            this.buttonText = 'Lỗi nén!';
                            setTimeout(() => {
                                this.buttonText = 'Nén Storage';
                                this.isCompressing = false;
                            }, 2500);
                        }
                    },

                    async compressImageBlob(blob, ext, targetMaxBytes = 950000) {
                        return new Promise(async (resolve) => {
                            try {
                                let imgBitmap = null;
                                let imgWidth = 0;
                                let imgHeight = 0;

                                try {
                                    imgBitmap = await createImageBitmap(blob);
                                    imgWidth = imgBitmap.width;
                                    imgHeight = imgBitmap.height;
                                } catch (e) {
                                    imgBitmap = null;
                                }

                                let imgElement = null;
                                if (!imgBitmap || !imgWidth || !imgHeight) {
                                    imgElement = new Image();
                                    imgElement.src = URL.createObjectURL(blob);
                                    await new Promise(r => {
                                        imgElement.onload = r;
                                        imgElement.onerror = r;
                                        setTimeout(r, 3000);
                                    });
                                    imgWidth = imgElement.width || 1200;
                                    imgHeight = imgElement.height || 800;
                                }

                                // Max resolution cap (2560px max dimension)
                                let maxDim = 2560;
                                let scale = 1.0;
                                if (imgWidth > maxDim || imgHeight > maxDim) {
                                    scale = Math.min(maxDim / imgWidth, maxDim / imgHeight);
                                }
                                let targetW = Math.max(10, Math.round(imgWidth * scale));
                                let targetH = Math.max(10, Math.round(imgHeight * scale));

                                const canvas = document.createElement('canvas');
                                canvas.width = targetW;
                                canvas.height = targetH;
                                const ctx = canvas.getContext('2d', {
                                    alpha: false
                                });

                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, targetW, targetH);

                                if (imgBitmap) {
                                    ctx.drawImage(imgBitmap, 0, 0, targetW, targetH);
                                    imgBitmap.close();
                                } else if (imgElement) {
                                    ctx.drawImage(imgElement, 0, 0, targetW, targetH);
                                }

                                // Export format
                                const mimeType = (ext === 'webp') ? 'image/webp' : 'image/jpeg';

                                // Attempt 1: High Quality (0.82)
                                let quality = 0.82;
                                let resultBlob = await new Promise(r => canvas.toBlob(r, mimeType, quality));

                                // Attempt 2: If still > target, reduce quality (0.72)
                                if (resultBlob && resultBlob.size > targetMaxBytes) {
                                    quality = 0.72;
                                    resultBlob = await new Promise(r => canvas.toBlob(r, mimeType, quality));
                                }

                                // Attempt 3: If still > target, scale down resolution slightly
                                if (resultBlob && resultBlob.size > targetMaxBytes) {
                                    const scaleDown = Math.min(0.85, Math.sqrt(targetMaxBytes / resultBlob
                                        .size));
                                    targetW = Math.max(10, Math.round(targetW * scaleDown));
                                    targetH = Math.max(10, Math.round(targetH * scaleDown));

                                    const canvas2 = document.createElement('canvas');
                                    canvas2.width = targetW;
                                    canvas2.height = targetH;
                                    const ctx2 = canvas2.getContext('2d', {
                                        alpha: false
                                    });
                                    ctx2.fillStyle = '#ffffff';
                                    ctx2.fillRect(0, 0, targetW, targetH);
                                    ctx2.drawImage(canvas, 0, 0, targetW, targetH);

                                    resultBlob = await new Promise(r => canvas2.toBlob(r, mimeType, 0.75));
                                }

                                resolve(resultBlob || blob);
                            } catch (e) {
                                console.error('Lỗi canvas compress:', e);
                                resolve(blob);
                            }
                        });
                    },

                    blobToBase64(blob) {
                        return new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result);
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        });
                    }
                };
            }
        </script>
    </x-filament::section>
</x-filament-widgets::widget>
