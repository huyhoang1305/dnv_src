<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BuildHandoverCommand extends Command
{
    /**
     * Tên và cú pháp lệnh Artisan.
     *
     * @var string
     */
    protected $signature = 'app:build-handover 
                            {--zip=true : Tu dong nen thanh tep ZIP hoan chinh}
                            {--obfuscate=true : Lam roi va ma hoa cac tep Admin Filament & Models}';

    /**
     * Mô tả lệnh Artisan.
     *
     * @var string
     */
    protected $description = 'Dong goi du an va ma hoa/lam roi phan Admin Filament 1-click de ban giao cho khach hang';

    public function handle()
    {
        $this->info('--> Bat dau quy trinh 1-Click dong goi & ma hoa du an ban giao...');

        $rootPath = base_path();
        $timestamp = date('Ymd_His');
        $distFolder = base_path('build_dist');
        $zipFileName = 'DU_AN_BAN_GIAO_'.$timestamp.'.zip';
        $zipFilePath = base_path($zipFileName);

        // 1. Dọn dẹp thư mục tạm cũ nếu có
        if (File::exists($distFolder)) {
            File::deleteDirectory($distFolder);
        }

        $this->info('[1/4] Dang copy ma nguon du an sang thu muc dong goi (build_dist)...');

        $excludePaths = [
            '.git',
            'node_modules',
            'vendor',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'build_dist',
            'scratch',
            'artisan.bat',
            'app/Console',
            'source.md',
            'md',
        ];

        $this->copyDirectory($rootPath, $distFolder, $excludePaths);

        // Đảm bảo cấu trúc thư mục storage & framework bắt buộc trong Laravel
        $requiredStorageDirs = [
            'storage/app/public',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];

        foreach ($requiredStorageDirs as $dir) {
            $fullDir = $distFolder.'/'.$dir;
            if (! File::exists($fullDir)) {
                File::makeDirectory($fullDir, 0755, true, true);
            }
            File::put($fullDir.'/.gitkeep', '');
        }

        // Tự động tạo tệp CSDL database.sqlite nếu chưa có (Tránh lỗi SQLiteDatabaseDoesNotExistException)
        $dbDir = $distFolder.'/database';
        if (! File::exists($dbDir)) {
            File::makeDirectory($dbDir, 0755, true, true);
        }
        if (File::exists(base_path('database/database.sqlite'))) {
            File::copy(base_path('database/database.sqlite'), $dbDir.'/database.sqlite');
        } else {
            File::put($dbDir.'/database.sqlite', '');
        }

        // 2. Làm rối & mã hóa toàn bộ mã nguồn ứng dụng (app, config, routes)
        if ($this->option('obfuscate') !== 'false') {
            $this->info('[2/4] Dang lam roi & ma hoa toan bo cac tep app, config & routes...');
            $this->obfuscateDirectory($distFolder.'/app');
            $this->obfuscateDirectory($distFolder.'/config');
            $this->obfuscateDirectory($distFolder.'/routes');
        }

        // 3. Chuẩn bị file .env sản phẩm
        $this->info('[3/4] Dang bao gom tep moi truong .env san pham...');
        if (File::exists(base_path('.env'))) {
            File::copy(base_path('.env'), $distFolder.'/.env');
        } elseif (File::exists($distFolder.'/.env.example')) {
            File::copy($distFolder.'/.env.example', $distFolder.'/.env');
        }

        // 4. Nén file ZIP
        if ($this->option('zip') !== 'false') {
            $this->info('[4/4] Dang nen toan bo du an thanh tep ZIP: '.$zipFileName.'...');
            $this->zipDirectory($distFolder, $zipFilePath);

            // Xóa thư mục tạm sau khi nén xong
            File::deleteDirectory($distFolder);

            $this->newLine();
            $this->components->info('DA DONG GOI & MA HOA HOAN THANH DU AN BAN GIAO!');
            $this->line('<fg=yellow>Tep ZIP ban giao san pham da duoc tao tai:</fg=yellow>');
            $this->line('<fg=green;options=bold>'.$zipFilePath.'</fg=green;options=bold>');
            $this->line('<fg=gray>Ma nguon tho tren may Local cua ban giu nguyen 100% khong bi anh huong.</fg=gray>');
        } else {
            $this->newLine();
            $this->components->info('DA XUAT THU MUC BAN GIAO TAI: '.$distFolder);
        }

        return Command::SUCCESS;
    }

    private function copyDirectory($src, $dst, $exclude = [])
    {
        $dir = opendir($src);
        @mkdir($dst, 0755, true);

        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $srcFile = $src.'/'.$file;
            $dstFile = $dst.'/'.$file;

            // Loại trừ tất cả các tệp markdown (.md)
            if (str_ends_with(strtolower($file), '.md')) {
                continue;
            }

            $relativePath = ltrim(str_replace(base_path(), '', $srcFile), '/\\');

            $shouldExclude = false;
            foreach ($exclude as $exc) {
                if ($relativePath === $exc || str_starts_with($relativePath, $exc.'/') || str_starts_with($relativePath, $exc.'\\') || str_ends_with($file, '.zip') || str_ends_with($file, '.sql')) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile, $exclude);
            } else {
                copy($srcFile, $dstFile);
            }
        }
        closedir($dir);
    }

    private function obfuscateDirectory($dirPath)
    {
        if (! File::exists($dirPath)) {
            return;
        }

        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $code = File::get($file->getRealPath());
                $obfuscatedCode = $this->obfuscatePhpCode($code);
                File::put($file->getRealPath(), $obfuscatedCode);
            }
        }
    }

    private function obfuscatePhpCode($code)
    {
        $cleanCode = trim($code);
        $prefix = chr(60).'?php';
        $suffix = '?'.chr(62);

        if (str_starts_with($cleanCode, $prefix)) {
            $cleanCode = substr($cleanCode, 5);
        }
        if (str_ends_with($cleanCode, $suffix)) {
            $cleanCode = substr($cleanCode, 0, -2);
        }

        $compressed = base64_encode(gzdeflate(trim($cleanCode), 9));

        $header = $prefix."\n";
        $payload = 'return eval(gzinflate(base64_decode("'.$compressed.'")));'."\n";

        return $header.$payload;
    }

    private function zipDirectory($sourcePath, $outZipPath)
    {
        // 1. Thử nén bằng PowerShell Compress-Archive chuẩn Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $winSource = str_replace('/', '\\', $sourcePath);
            $winZip = str_replace('/', '\\', $outZipPath);
            $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "Compress-Archive -Path \''.$winSource.'\*\' -DestinationPath \''.$winZip.'\' -Force"';
            @exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && File::exists($outZipPath)) {
                return;
            }
        }

        // 2. Fallback bằng ZipArchive
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                $count = 0;
                foreach ($files as $name => $file) {
                    if (! $file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($sourcePath) + 1);
                        $zip->addFile($filePath, $relativePath);
                        $count++;

                        if ($count % 500 === 0) {
                            $zip->close();
                            $zip->open($outZipPath, ZipArchive::CREATE);
                        }
                    }
                }
                @$zip->close();
            }
        }
    }
}
