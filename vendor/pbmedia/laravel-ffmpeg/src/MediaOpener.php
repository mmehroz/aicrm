<?php

namespace ProtoneMedia\LaravelFFMpeg;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Media\AbstractMediaType;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use ProtoneMedia\LaravelFFMpeg\Drivers\PHPFFMpeg;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Exporters\MediaExporter;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Disk;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use ProtoneMedia\LaravelFFMpeg\Filesystem\MediaCollection;
use ProtoneMedia\LaravelFFMpeg\Filesystem\MediaOnNetwork;
use ProtoneMedia\LaravelFFMpeg\Filesystem\TemporaryDirectories;

/**
 * @mixin \ProtoneMedia\LaravelFFMpeg\Drivers\PHPFFMpeg
 */
class MediaOpener
{
    use ForwardsCalls;

    /**
     * @var \ProtoneMedia\LaravelFFMpeg\Filesystem\Disk
     */
    private $disk;

    /**
     * @var \ProtoneMedia\LaravelFFMpeg\Drivers\PHPFFMpeg
     */
    private $driver;

    /**
     * @var \ProtoneMedia\LaravelFFMpeg\Filesystem\MediaCollection
     */
    private $collection;

    /**
     * @var \FFMpeg\Coordinate\TimeCode
     */
    private $timecode;

    /**
     * Uses the 'filesystems.default' disk from the config if none is given.
     * Gets the underlying PHPFFMpeg instance from the container if none is given.
     * Instantiates a fresh MediaCollection if none is given.
     */
    public function __construct($disk = null, PHPFFMpeg $driver = null, MediaCollection $mediaCollection = null)
    {
        $this->disk = Disk::make($disk ?: config('filesystems.default'));

        $this->driver = ($driver ?: app(PHPFFMpeg::class))->fresh();

        $this->collection = $mediaCollection ?: new MediaCollection;
    }

    public function clone(): self
    {
        return new MediaOpener(
            $this->disk,
            $this->driver,
            $this->collection
        );
    }

    /**
     * Set the disk to open files from.
     */
    public function fromDisk($disk): self
    {
        $this->disk = Disk::make($disk);

        return $this;
    }

    /**
     * Alias for 'fromDisk', mostly for backwards compatibility.
     */
    public function fromFilesystem(Filesystem $filesystem): self
    {
        return $this->fromDisk($filesystem);
    }

    /**
     * Instantiates a Media object for each given path.
     */
    public function open($paths): self
    {
        foreach (Arr::wrap($paths) as $path) {
            $this->collection->push(Media::make($this->disk, $path));
        }

        return $this;
    }

    /**
     * Instantiates a MediaOnNetwork object for each given url.
     */
    public function openUrl($paths, array $headers = []): self
    {
        foreach (Arr::wrap($paths) as $path) {
            $this->collection->push(MediaOnNetwork::make($path, $headers));
        }

        return $this;
    }

    public function get(): MediaCollection
    {
        return $this->collection;
    }

    public function getDriver(): PHPFFMpeg
    {
        return $this->driver->open($this->collection);
    }

    /**
     * Forces the driver to open the collection with the `openAdvanced` method.
     */
    public function getAdvancedDriver(): PHPFFMpeg
    {
        return $this->driver->openAdvanced($this->collection);
    }

    /**
     * Shortcut to set the timecode by string.
     */
    public function getFrameFromString(string $timecode): self
    {
        return $this->getFrameFromTimecode(TimeCode::fromString($timecode));
    }

    /**
     * Shortcut to set the timecode by seconds.
     */
    public function getFrameFromSeconds(float $seconds): self
    {
        return $this->getFrameFromTimecode(TimeCode::fromSeconds($seconds));
    }

    public function getFrameFromTimecode(TimeCode $timecode): self
    {
        $this->timecode = $timecode;

        return $this;
    }

    /**
     * Returns an instance of MediaExporter with the driver and timecode (if set).
     */
    public function export(): MediaExporter
    {
        return tap(new MediaExporter($this->getDriver()), function (MediaExporter $mediaExporter) {
            if ($this->timecode) {
                $mediaExporter->frame($this->timecode);
            }
        });
    }

    /**
     * Returns an instance of HLSExporter with the driver forced to AdvancedMedia.
     */
    public function exportForHLS(): HLSExporter
    {
        return new HLSExporter($this->getAdvancedDriver());
    }

    public function cleanupTemporaryFiles(): self
    {
        TemporaryDirectories::deleteAll();

        return $this;
    }

    public function each($items, callable $callback): self
    {
        Collection::make($items)->each(function ($item, $key) use ($callback) {
            return $callback($this->clone(), $item, $key);
        });

        return $this;
    }

    /**
     * Returns the Media object from the driver.
     */
    public function __invoke(): AbstractMediaType
    {
        return $this->getDriver()->get();
    }

    /**
     * Forwards all calls to the underlying driver.
     * @return void
     */
    public function __call($method, $arguments)
    {
        $result = $this->forwardCallTo($driver = $this->getDriver(), $method, $arguments);

        return ($result === $driver) ? $this : $result;
    }
}
