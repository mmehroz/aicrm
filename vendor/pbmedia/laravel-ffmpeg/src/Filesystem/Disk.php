<?php

namespace ProtoneMedia\LaravelFFMpeg\Filesystem;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\ForwardsCalls;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem as LeagueFilesystem;

/**
 * @mixin \Illuminate\Filesystem\FilesystemAdapter
 */
class Disk
{
    use ForwardsCalls;

    /**
     * @var string|\Illuminate\Contracts\Filesystem\Filesystem
     */
    private $disk;

    /**
     * @var string
     */
    private $temporaryDirectory;

    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private $filesystemAdapter;

    public function __construct($disk)
    {
        $this->disk = $disk;
    }

    /**
     * Little helper method to instantiate this class.
     */
    public static function make($disk): self
    {
        if ($disk instanceof self) {
            return $disk;
        }

        return new static($disk);
    }

    public static function makeTemporaryDisk(): self
    {
        $filesystemAdapter = app('filesystem')->createLocalDriver([
            'root' => TemporaryDirectories::create(),
        ]);

        return new static($filesystemAdapter);
    }

    /**
     * Creates a fresh instance, mostly used to force a new TemporaryDirectory.
     */
    public function clone(): self
    {
        return new Disk($this->disk);
    }

    /**
     * Creates a new TemporaryDirectory instance if none is set, otherwise
     * it returns the current one.
     */
    public function getTemporaryDirectory(): string
    {
        if ($this->temporaryDirectory) {
            return $this->temporaryDirectory;
        }

        return $this->temporaryDirectory = TemporaryDirectories::create();
    }

    public function makeMedia(string $path): Media
    {
        return Media::make($this, $path);
    }

    /**
     * Returns the name of the disk. It generates a name if the disk
     * is an instance of Flysystem.
     */
    public function getName(): string
    {
        if (is_string($this->disk)) {
            return $this->disk;
        }

        return get_class($this->getFlysystemAdapter()) . "_" . md5(json_encode(serialize($this->getFlysystemAdapter())));
    }

    public function getFilesystemAdapter(): FilesystemAdapter
    {
        if ($this->filesystemAdapter) {
            return $this->filesystemAdapter;
        }

        if ($this->disk instanceof Filesystem) {
            return $this->filesystemAdapter = $this->disk;
        }

        return $this->filesystemAdapter = Storage::disk($this->disk);
    }

    private function getFlysystemDriver(): LeagueFilesystem
    {
        return $this->getFilesystemAdapter()->getDriver();
    }

    private function getFlysystemAdapter(): AdapterInterface
    {
        return $this->getFlysystemDriver()->getAdapter();
    }

    public function isLocalDisk(): bool
    {
        return $this->getFlysystemAdapter() instanceof Local;
    }

    /**
     * Forwards all calls to Laravel's FilesystemAdapter which will pass
     * dynamic methods call onto Flysystem.
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getFilesystemAdapter(), $method, $parameters);
    }
}
