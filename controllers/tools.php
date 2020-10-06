<?php
namespace Concrete\Package\SimpleGallery\Controller;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\File\Image\Thumbnail\Type\Version as ThumbnailTypeVersion;
use HtmlObject\Image as HtmlImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Controller;
use Permissions;
use File;

class Tools extends Controller
{
    public function getFileDetails()
    {
        $files = [];
        $fileIDs = $this->request->request('fID', []);

        if (!is_array($fileIDs)) {
            $fileIDs = [$fileIDs];
        }

        $fileManagerDetailThumb = ThumbnailTypeVersion::getByHandle('file_manager_detail');

        foreach ($fileIDs as $fID) {
            $f = File::getByID($fID);
            $fp = new Permissions($f);
            if (is_object($f) && $fp->canViewFileInFileManager()) {
                $fv = $f->getApprovedVersion();
                $thumbnailPath = $fv->getThumbnailURL($fileManagerDetailThumb);
                try {
                    $fso = $f->getFileStorageLocationObject()->getFileSystemObject();
                    if (!$fso->has($fileManagerDetailThumb->getFilePath($fv))) {
                        $fv->generateThumbnail($fileManagerDetailThumb);
                    }
                } catch (\Exception $e) {
                    // Does nothing, we only give a try to generate thumbnail
                }

                $files[] = [
                    'fID' => $fID,
                    'title' => $fv->getTitle(),
                    'caption' => $fv->getDescription(),
                    'alt' => $fv->getAttribute('alt'),
                    'copyright' => $fv->getAttribute('copyright'),
                    'resultsThumbnailImg' => (string) HtmlImage::create($thumbnailPath),
                ];
            }
        }

        if (0 == count($files)) {
            return JsonResponse::create(t('File not found.'), JsonResponse::HTTP_BAD_REQUEST);
        }

        return JsonResponse::create($files);
    }
}
