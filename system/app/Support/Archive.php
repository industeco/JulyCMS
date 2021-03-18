<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class Archive
{
    /**
     * 根目录
     *
     * @var string
     */
    protected $root;

    /**
     * 打包文件名
     *
     * @var string
     */
    protected $fileName;

    /**
     * 所有文件/目录
     *
     * @var array
     */
    protected $files = [];

    /**
     * 重命名
     *
     * @var array
     */
    protected $renames = [];

    /**
     * @param  string $fileName 最终生成的文件名
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->root = $this->normalizePath(public_path()).'/';
    }

    /**
     * 快捷创建
     *
     * @param string $fileName 文件名
     */
    public static function create(string $fileName)
    {
        return new static($fileName);
    }

    /**
     * 设置根目录
     *
     * @param  string $root
     * @return self
     */
    public function root(string $root)
    {
        $root = trim($root, ' \\/');
        if ($root) {
            $this->root = $this->normalizePath($root).'/';
        }
        return $this;
    }

    /**
     * 将路径分隔符统一替换为 '/'
     *
     * @param  string $path
     * @return string
     */
    protected function normalizePath(string $path)
    {
        return str_replace('\\', '/', $path);
    }

    // protected function recoverPath(string $path)
    // {
    //     return str_replace('/', \DIRECTORY_SEPARATOR, $path);
    // }

    /**
     * 获取文件列表，由 $files 与 $renames 融合而成
     *
     * @return array
     */
    public function getFiles()
    {
        if (empty($this->renames)) {
            return array_fill_keys($this->files, null);
        }

        // $files = str_ireplace(
        //     array_keys($this->renames),
        //     array_values($this->renames),
        //     $this->files
        // );

        // return array_combine($this->files, $files);

        $files = [];
        foreach ($this->files as $fileName) {
            $files[$fileName] = null;
            foreach ($this->renames as $path => $newPath) {
                $len = strlen($path);
                if (0 === strncasecmp($fileName, $path, $len)) {
                    $files[$fileName] = $newPath.substr($fileName, $len);
                    break;
                }
            }
        }

        return $files;
    }

    /**
     * 从 AllowList 获取文件
     *
     * @param  array $allowList
     * @return self
     */
    public function from(array $allowList)
    {
        $this->files = $this->resolveAllowList('', $allowList);

        return $this;
    }

    /**
     * 解析 AllowList，生成文件列表
     *
     * @param  string $root
     * @param  array $allowList
     * @return array
     */
    protected function resolveAllowList(string $root, array $allowList)
    {
        $files = [];
        foreach ($allowList as $path => $newPath) {
            if (is_array($newPath)) {
                $files = array_merge($files, $this->resolveAllowList(Str::finish($root.$path, '/'), $newPath));
                $newPath = $newPath[':rename'] ?? null;
            } elseif (is_int($path)) {
                $path = $newPath;
                $newPath = null;
            }

            if ($newPath) {
                $this->renames[$root.$path] = $root.$newPath;
            }

            $files = array_merge($files, $this->getFilesFromPath($root.$path));
        }

        return $files;
    }

    /**
     * 从给定路径模式获取文件
     *
     * @param  string $path
     * @return array
     */
    protected function getFilesFromPath(string $path)
    {
        $fullPath = $this->getFullPath($path);

        if (false === strpos($path, '*')) {
            if (is_dir($fullPath) || is_file($fullPath)) {
                return [$path];
            }
        }

        $files = [];
        if ($finder = $this->getFinder($fullPath)) {
            foreach ($finder as $file) {
                $files[] = $this->getRelativePath($file->getRealPath());
            }
            if (empty($files) && is_dir($fullPath)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * 使用给定路径模式生成一个 Symfony\Finder
     *
     * @param  string $path
     * @return \Symfony\Component\Finder\Finder|null
     */
    protected function getFinder(string $path)
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);
        $pattern = basename($fullPath);
        if ('/' === substr($path, -1)) {
            $pattern .= '/';
        }

        try {
            $finder = Finder::create()->in($dir);

            switch ($pattern) {
                case '*':   // 目录下的文件和文件夹
                    return $finder->depth(0);

                case '**':  // 目录下的所有文件和文件夹
                    return $finder;

                case '*/':  // 目录下的文件夹
                    return $finder->directories()->depth(0);

                case '**/': // 目录下的所有文件夹
                    return $finder->directories();

                case '*.*': // 目录下的文件
                    return $finder->files()->depth(0);

                case '**.*':    // 目录下的所有文件
                    return $finder->files();
            }

            return $finder->name($pattern);

        } catch (\Throwable $th) {
            if (! $th instanceof DirectoryNotFoundException) {
                throw $th;
            }
        }

        return null;
    }

    /**
     * 获取相对于 $root 的子路径
     *
     * @param  string $path
     * @return string
     */
    protected function getRelativePath(string $path)
    {
        $path = $this->normalizePath($path);
        if (0 === stripos($path, $this->root)) {
            return substr($path, strlen($this->root));
        }
        return $path;
    }

    /**
     * 获取基于 $root 的完整路径
     *
     * @param  string $path
     * @return string
     */
    protected function getFullPath(string $path)
    {
        $path = $this->normalizePath($path);
        if (0 !== stripos($path, $this->root)) {
            return $this->root.ltrim($path, '/');
        }
        return $path;
    }

    /**
     * 使用 zip 格式打包文件
     *
     * @param  string  $fileName 文件名
     * @return bool
     */
    public function zip(string $fileName = null)
    {
        $fileName = $fileName ?: $this->fileName;
        if ('.zip' !== substr($fileName, -4)) {
            $fileName = preg_replace('/[\s.]*$/', '.zip', $fileName);
        }

        $zip = new ZipArchive();
        $zip->open($this->getFullPath($fileName), ZipArchive::OVERWRITE|ZipArchive::CREATE);

        foreach ($this->getFiles() as $path => $newPath) {
            $fullPath = $this->getFullPath($path);
            if (is_file($fullPath)) {
                $zip->addFile($fullPath, $newPath ?: $path);
            } else {
                $zip->addEmptyDir(Str::finish($path, '/'));
            }
        }

        $zip->close();

        return true;
    }
}
