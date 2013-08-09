<?php

class UnisinosController extends AppController {

  public $name = 'Unisinos';

  public $layout = 'main';

  public function beforeFilter() {
    if ($this->request->params['action'] != 'index' && $this->request->params['action'] != 'message') {
        $this->redirect('index');
    }
  }

  public function index() {
    $this->Session->delete('Unisinos');
    $this->redirect('message');
  }

  public function message() {

  }
  
}

?>