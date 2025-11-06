<?php
require_once 'BaseController.php';
class PageController extends BaseController {
    public function show($view) {
        $this->render($view);
    }
} 