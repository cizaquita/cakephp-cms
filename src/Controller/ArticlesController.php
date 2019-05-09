<?php
// src/Controller/ArticlesController.php

namespace App\Controller;

class ArticlesController extends AppController
{

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Flash'); // Include the FlashComponent
        $this->Auth->allow(['tags']);
    }

    public function index()
    {
        $this->loadComponent('Paginator');
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }

    public function view($slug = null)
	{
	    $article = $this->Articles->findBySlug($slug)->contain(['Tags'])->firstOrFail();
	    $this->set(compact('article'));
	}

	//Add new Article
    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {

        	pr($this->request->getData());
        	debug($this->request->getData());

            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Hardcoding the user_id is temporary, and will be removed later
            // when we build authentication out.
            //$article->user_id = 1;

            // Changed: Set the user_id from the session.
        	$article->user_id = $this->Auth->user('id');

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved. 🤷'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.👿'));
        }
        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list');
        // Set tags to the view context
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    //Edit Article
    //Param: slug
    public function edit($slug)
	{
	    $article = $this->Articles->findBySlug($slug)->contain(['Tags'])->firstOrFail();//loads associate tags contain['Tags']
	    //Note the methods inside array of string, is different as above
	    if ($this->request->is(['post', 'put'])) {
	        $this->Articles->patchEntity($article, $this->request->getData(), [
	            // Added: Disable modification of user_id.
	            'accessibleFields' => ['user_id' => false]
	        ]);
	        if ($this->Articles->save($article)) {
	            $this->Flash->success(__('Your article has been updated. 🤷'));
	            return $this->redirect(['action' => 'index']);
	        }
	        $this->Flash->error(__('Unable to update your article.👿'));
	    }
	    // Get a list of tags.
	    $tags = $this->Articles->Tags->find('list');
	    // Set tags to the view context
	    $this->set('tags', $tags);

	    $this->set('article', $article);
	}

	//Delete Article
	//Param slug
	public function delete($slug)
	{
	    $this->request->allowMethod(['post', 'delete']);//AllowedMethod throws an exception

	    $article = $this->Articles->findBySlug($slug)->firstOrFail();
    	debug($article);

	    if ($this->Articles->delete($article)) {
	        $this->Flash->success(__('The {0} article has been deleted.🤷', $article->title));
	        return $this->redirect(['action' => 'index']);
	    }
	}

	//find by tags "tagged"
/*	public function tags()
	{
	    // The 'pass' key is provided by CakePHP and contains all
	    // the passed URL path segments in the request.
	    $tags = $this->request->getParam('pass');

	    // Use the ArticlesTable to find tagged articles.
	    $articles = $this->Articles->find('tagged', [
	        'tags' => $tags
	    ]);

	    // Pass variables into the view template context.
	    $this->set([
	        'articles' => $articles,
	        'tags' => $tags
	    ]);
	}
*/
	//same as above but using variadic argument
	public function tags(...$tags)
	{
	    // Use the ArticlesTable to find tagged articles.
	    $articles = $this->Articles->find('tagged', [
	        'tags' => $tags
	    ]);

	    // Pass variables into the view template context.
	    $this->set([
	        'articles' => $articles,
	        'tags' => $tags
	    ]);
	}

	//
	public function isAuthorized($user)
	{
	    $action = $this->request->getParam('action');
	    // The add and tags actions are always allowed to logged in users.
	    if (in_array($action, ['add', 'tags'])) {
	        return true;
	    }

	    // All other actions require a slug.
	    $slug = $this->request->getParam('pass.0');
	    if (!$slug) {
	        return false;
	    }

	    // Check that the article belongs to the current user.
	    $article = $this->Articles->findBySlug($slug)->first();

	    return $article->user_id === $user['id'];
	}
}