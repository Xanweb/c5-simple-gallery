<?php
namespace Concrete\Package\SimpleGallery\Controller\File;

use Concrete\Controller\Backend\UserInterface as BackendInterfaceController;

class Properties extends BackendInterfaceController
{
    protected $viewPath = '/dialogs/file/properties';

    protected function canAccess()
    {
        return true;
    }

    public function view()
    {
        $this->set('f', \File::getByID($this->request->request('fID')));
        $this->set('ftitle', $this->request->request('ftitle'));
        $this->set('caption', $this->request->request('caption'));
        $this->set('altText', $this->request->request('altText'));
        $this->set('copyright', $this->request->request('copyright'));
        $this->set('showCopyright', $this->request->request('showCopyright'));
    }
}
