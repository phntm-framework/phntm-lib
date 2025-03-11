<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;
use Nyholm\Psr7\UploadedFile;
use Phntm\Lib\Images\BaseImage;
use Phntm\Lib\Di\Container;
use Psr\Http\Message\ServerRequestInterface;
use function get_class;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Image extends Base
{
    public string $columnType = 'string';

    public string $inputTemplate = 'file';

    public function __construct(
        public ?string $label = null,
        public string $placeholder = '',
        public bool $required = false,
    ) {
        $this->registerHook('beforeSave', function () {


            /** @var BaseImage $old */
            $old = $this->getOldValue();
            $oldLocation = $old->getSrc();

            /** @var ServerRequestInterface $request */
            $request = Container::get()->get(ServerRequestInterface::class);
            $files = $request->getUploadedFiles()[static::getColumnName() . '.new'];
            dd($files, $oldLocation, $old);

            $location = $this->getFileSaveLocation($files, $this->model);
            $newImage = new BaseImage($location);

            $this->model->{static::getColumnName()} = $newImage;
        });
    }

    public function getOptions(): array
    {
        return [
            'length' => 255,
            ...$this->getBaseOptions(),
        ];
    }

    public function getFileSaveLocation(UploadedFile $file, $model): string
    {
        return ROOT . '/images/uploads/' . $this->getModelHash($file, $model);
    }

    public function saveFile(UploadedFile $file, $model): string
    {
        try {
            $filename = $file->getClientFilename();
            $location = $this->getFileSaveLocation($file, $model);

            if (!is_dir($location)) {
                mkdir($location, 0777, true);
            }

            $file->moveTo($location . '/' . $filename);

            return $location . '/' . $filename;

        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function getModelHash(UploadedFile $file, $model): string
    {
        return md5(get_class($model) . '_' . $model->id);
    }

    public function fromDbValue($value): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return new BaseImage($value);
    }

    public function fromFormValue($value): mixed
    {
        return new BaseImage($value);
    }

    public function getFormAttributes(): array
    {
        $input = parent::getFormAttributes();

        return $input;
    }
}
