<?php

namespace App\Helpers;

use ZipArchive;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class ZipHelper
{
    protected $cb_show;
    protected $type;

    protected $password;
    protected $files;
    public function __construct(callable  $cb_show = null)
    {
        $this->cb_show = isset($cb_show) ? $cb_show : function ($mes) {
        };
    }
    public function showMessage($message)
    {
        $cb_show = $this->cb_show;
        $cb_show($message);
    }
    private  function checkAndSetZipMethod()
    {
        $this->type = 'php';
        //check 7zip
        $cmd = "7z";
        $dir_7z = config('app.group_export.dir_7z');
        if (!empty($dir_7z)) {
            $cmd = "\"$dir_7z/$cmd\" --help";
        }
        exec($cmd, $output, $status);
        if ($status == 0) {
            $this->type = '7z';
            return;
        }
        //check zip
        $cmd = "zip -h";
        exec($cmd, $output, $status);
        if ($status == 0) {
            $this->type = 'zip';
            return;
        }
    }
    private  function checkAndSetUnZipMethod()
    {
        $this->type = 'php';
        //check 7zip
        $cmd = "7z";
        $dir_7z = config('app.group_export.dir_7z');
        if (!empty($dir_7z)) {
            $cmd = "\"$dir_7z/$cmd\" --help";
        }
        exec($cmd, $output, $status);
        if ($status == 0) {
            $this->type = '7z';
            return;
        }
        //check zip
        $cmd = "unzip -h";
        exec($cmd, $output, $status);
        if ($status == 0) {
            $this->type = 'unzip';
            return;
        }
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }
    public function addFolder($path)
    {
        $this->files[] = ['path' => $path, 'is_folder' => true];
    }
    public function zip($output, $option = [])
    {
        $this->checkAndSetZipMethod();
        $password = $option['password']  ?? '';
        if (empty($password)) {
            $password = $this->password;
        }
        switch ($this->type) {
            case 'zip':
                $this->showMessage("zip file by zip");
                foreach ($this->files as $file) {
                    $cd = "cd " . $file['path'] . ' && cd ../ && ';
                    $folder_name = explode("/", $file['path']);
                    $folder_name = end($folder_name);
                    $cmd = "zip";
                    if (isset($file['is_folder']) && $file['is_folder']) {
                        $cmd = "$cmd -r {$output} {$folder_name}";
                    } else {
                        $cmd = "$cmd a {$output} {$folder_name}";
                    }
                    if (!empty($password)) {
                        $cmd .= " -e -P '{$password}'";
                    }
                    $cmd = $cd . $cmd;
                    $this->runCmd($cmd);
                }
                break;
            case '7z':
                $this->showMessage("zip file by 7zip");
                foreach ($this->files as $file) {
                    $cmd = "7z";
                    $dir_7z = config('app.group_export.dir_7z');
                    if (!empty($dir_7z)) {
                        $cmd = "\"$dir_7z/$cmd\"";
                    }
                    if (isset($file['is_folder']) && $file['is_folder']) {
                        $cmd = "$cmd a {$output} {$file['path']}/*";
                    } else {
                        $cmd = "$cmd a {$output} {$file['path']}";
                    }
                    if (!empty($password)) {
                        $cmd .= " -p\"{$password}\"";
                    }
                    $this->runCmd($cmd);
                }
                break;
            case 'php':
                $this->showMessage("zip file by php");
                $zip = new ZipArchive();
                $zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $zip->setPassword($password);
                foreach ($this->files as $file) {
                    if (isset($file['is_folder']) && $file['is_folder']) {
                        $rootPath = $file['path'];
                        // Create recursive directory iterator
                        /** @var SplFileInfo[] $files */
                        $files = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($rootPath),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        foreach ($files as $name => $file) {
                            // Skip directories (they would be added automatically)
                            if (!$file->isDir()) {
                                // Get real and relative path for current file
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($rootPath) + 1);

                                // Add current file to archive
                                $zip->addFile($filePath, $relativePath);
                                if (!empty($password)) {
                                    $zip->setEncryptionName($relativePath, ZipArchive::EM_AES_256);
                                }
                            }
                        }
                    } else {
                        $filePath = $file['path'];
                        $relativePath = $file['name'] ?? str_replace(dirname($filePath) . '/', '', $filePath);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                break;
            default:
                throw new \Exception('Không tìm được phương thức để nén tập tin');
        }
    }
    public function unzip($input, $output, $option = [])
    {
        $this->checkAndSetUnZipMethod();
        $password = $option['password']  ?? '';
        if (empty($password)) {
            $password =  $this->password;
        }
        $status = 1;
        switch ($this->type) {
            case 'unzip':
                $this->showMessage("unzip file by unzip");
                $cmd = "unzip";
                if (!empty($password)) {
                    $cmd .= " -P '{$password}'";
                }
                $cmd = "$cmd $input -d $output";
                $this->runCmd($cmd);
                break;
            case '7z':
                $this->showMessage("unzip file by 7zip");
                $cmd = "7z";
                $dir_7z = config('app.group_export.dir_7z');
                if (!empty($dir_7z)) {
                    $cmd = "\"$dir_7z/$cmd\"";
                }
                $cmd = "$cmd x $input -o\"$output\" -y";
                if (!empty($password)) {
                    $cmd .= " -p\"{$password}\"";
                }
                $this->runCmd($cmd);
                break;
            case 'php':
                $this->showMessage("unzip file by php");
                $zip = new ZipArchive();
                $res = $zip->open($input);
                if (!empty($password)) {
                    $zip->setPassword($password);
                }
                if ($res === TRUE) {
                    $zip->extractTo($output);
                    $zip->close();
                } else {
                    throw new \Exception('Tập tin không đọc được');
                }
                break;
            default:
                throw new \Exception('Không tìm được phương thức để giải nén tập tin');
        }
    }
    private function runCmd($cmd)
    {
        $process = Process::fromShellCommandline($cmd);
        $timeout = 60 * 60 * 24;
        $process->setTimeout($timeout);
        $process->run(function ($type, $buffer) {
            $this->showMessage("\n$type: \n $buffer");
        });
        if (!$process->isSuccessful()) {
            $this->showMessage($process->getErrorOutput());
            throw new \Exception($process->getErrorOutput());
        }
    }
}
