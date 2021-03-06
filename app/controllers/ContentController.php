<?php

class ContentController extends Zend_Controller_Action {

    public function init() {
	/* Initialize action controller here */
    }

    public function indexAction() {
	// action body
    }

    public function newAction() {
	if ($this->getRequest()->getMethod() == 'POST') {
	    // check acl
	    if(!Knowledgeroot_Acl::iAmAllowed('page_'.$this->_getParam('content_page'), 'new_content'))
		    $this->_redirect('page/' . $this->_getParam('content_page'));

	    if ($this->_getParam('button') == 'close')
		$this->_redirect('page/' . $this->_getParam('content_page'));

	    $content = new Knowledgeroot_Content();
	    $content->setName($this->_getParam('content_title'));
	    $content->setContent($this->_getParam('content'));
	    $content->setParent($this->_getParam('content_page'));
	    $content->setAcl(json_decode($this->_getParam('acl')));
	    $content->save();

	    // save tags
	    if($this->_getParam('content_tags') != '') {
		$tags = explode(",", $this->_getParam('content_tags'));

		foreach($tags as $tag) {
		    if(trim($tag) != '') {
			$newTag = new Knowledgeroot_Tag();
			$newTag->setName(trim($tag));
			$newTag->save();

			$content->addTag($newTag);
		    }
		}
	    }

	    if ($this->_getParam('button') == 'save') {
		$this->_redirect('content/edit/' . $content->getId());
	    } else {
		$this->_redirect('page/' . $this->_getParam('content_page') . '#content' . $content->getId());
	    }
	} else {
	    $this->view->action = 'new';

	    $rte = Knowledgeroot_Registry::get('rte');
	    $rte->setName('content');
	    $rte->setContent('');
	    $this->view->editor = $rte;

	    $this->view->page = $this->_getParam('id');
	    $parent = new Knowledgeroot_Page($this->_getParam('id'));
	    $this->view->pagename = $parent->getName();

	    $this->renderScript("content/content.phtml");
	}
    }

    public function editAction() {
	// check acl
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'edit'))
		$this->_redirect('page/' . $this->_getParam('content_page'));

	if ($this->getRequest()->getMethod() == 'POST') {
	    if ($this->_getParam('button') == 'close')
		$this->_redirect('page/' . $this->_getParam('content_page'));

	    $content = new Knowledgeroot_Content($this->_getParam('id'));
	    $content->setName($this->_getParam('content_title'));
	    $content->setContent($this->_getParam('content'));
	    $content->setParent($this->_getParam('content_page'));
	    $content->setAcl(json_decode($this->_getParam('acl')));
	    $content->save();

	    // delete existing tags
	    $content->deleteTags();

	    // save tags
	    if($this->_getParam('content_tags') != '') {
		$tags = explode(",", $this->_getParam('content_tags'));

		foreach($tags as $tag) {
		    if(trim($tag) != '') {
			$newTag = new Knowledgeroot_Tag();
			$newTag->setName(trim($tag));
			$newTag->save();

			$content->addTag($newTag);
		    }
		}
	    }

	    if ($this->_getParam('button') == 'save') {
		$this->_redirect('content/edit/' . $content->getId());
	    } else {
		$this->_redirect('page/' . $this->_getParam('content_page') . '#content' . $content->getId());
	    }
	} else {
	    $this->view->action = 'edit';
	    $this->view->id = $this->_getParam('id');

	    $content = new Knowledgeroot_Content($this->_getParam('id'));

	    $rte = Knowledgeroot_Registry::get('rte');
	    $rte->setName('content');
	    $rte->setContent($content->getContent(true));
	    $this->view->editor = $rte;

	    $this->view->title = $content->getName();
	    $this->view->tags = $content->getTags();

	    $this->view->page = $content->getParent();
	    $parent = new Knowledgeroot_Page($content->getParent());
	    $this->view->pagename = $parent->getName();

	    $this->view->created_by = $content->getCreatedBy()->getLogin();
	    $this->view->create_date = $content->getCreateDate()->getUserDate();

	    $this->view->versions = $content->getVersions();

	    $this->renderScript("content/content.phtml");
	}
    }

    public function deleteAction() {
	// check acl
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'delete'))
		$this->_redirect('page/' . $this->_getParam('content_page'));

	$content = new Knowledgeroot_Content($this->_getParam('id'));
	$parent = $content->getParent();
	$content->delete();

	$this->_redirect('page/' . $parent);
    }

    public function moveAction() {
	// acl checks
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'edit'))
		$this->_redirect('');

	// using blank layout
	$this->_helper->layout->setLayout('blank');

	if($this->_getParam('target') !== null) {
	    // check if user has page new_content rights on target
	    if(!Knowledgeroot_Acl::iAmAllowed('page_'.$this->_getParam('target'), 'new_content'))
		    $this->_redirect('page/' . $this->_getParam('id'));

	    $content = new Knowledgeroot_Content($this->_getParam('id'));
	    $content->setParent($this->_getParam('target'));
	    $content->save();

	    $this->view->pageid = $content->getParent();
	    $this->view->contentid = $this->_getParam('id');
	    $this->view->target = $this->_getParam('target');
	} else {
	    $this->view->contentid = $this->_getParam('id');
	}
    }

    public function movedownAction() {
	// acl checks
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'edit'))
		$this->_redirect('');

	// get content and move down
	$content = new Knowledgeroot_Content($this->_getParam('id'));
	$content->moveDown();
	$parent = $content->getParent();
	//$content->save();

	// redirect to page
	$this->_redirect('page/' . $parent);
    }

    public function moveupAction() {
	// acl checks
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'edit'))
		$this->_redirect('');

	// get content and move up
	$content = new Knowledgeroot_Content($this->_getParam('id'));
	$content->moveUp();
	$parent = $content->getParent();
	//$content->save();

	// redirect to page
	$this->_redirect('page/' . $parent);
    }

    public function printAction() {
	// check acl
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'print'))
		$this->_redirect('');

	// using blank layout
	$this->_helper->layout->setLayout('blank');

	$content = new Knowledgeroot_Content($this->_getParam('id'));
	$this->view->title = $content->getName();
	$this->view->content = $content->getContent();
	$this->view->bodyTags = 'onload="window.print();"';
    }

    public function showAction() {
	// check acl
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'show'))
		$this->_redirect('');

	// using blank layout
	$this->_helper->layout->setLayout('blank');

	if($this->_getParam('version') !== null) {
	    $content = new Knowledgeroot_Content($this->_getParam('id'), $this->_getParam('version'));
	} else {
	    $content = new Knowledgeroot_Content($this->_getParam('id'));
	}

	$this->view->title = $content->getName();
	$this->view->content = $content->getContent();
    }

    public function selectAction() {
	// using blank layout
	$this->_helper->layout->setLayout('blank');

	if($this->_getParam('id') != null) {
	    $this->view->contents = Knowledgeroot_Content::getContents(new Knowledgeroot_Page($this->_getParam('id')));
	}
    }

    public function restoreAction() {
	// acl checks
	if(!Knowledgeroot_Acl::iAmAllowed('content_'.$this->_getParam('id'), 'edit'))
		$this->_redirect('');

	// get content and restore version
	$content = new Knowledgeroot_Content($this->_getParam('id'), $this->_getParam('version'));
	$content->restore();
	$parent = $content->getParent();

	// show success message
	Knowledgeroot_Message::success("Content restored","Content was restored to version " . $this->_getParam('version'));

	// redirect to page
	$this->_redirect('page/' . $parent);
    }
}

