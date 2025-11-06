<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/Sponsorship.php';

class HomeController extends BaseController {
    
    public function index() {
        // Get featured innovations
        $featuredInnovations = $this->innovation->getFeatured();
        
        // Get categories with innovation counts
        $categories = $this->category->getWithInnovationCount();
        
        // Get recent innovations
        $recentInnovations = $this->innovation->getAllWithDetails(1, [])['data'];
        
        // Get platform statistics
        $stats = $this->innovation->getStats();
        
        // Get current user if logged in
        $currentUser = $this->getCurrentUser();
        
        $this->render('home', [
            'featuredInnovations' => $featuredInnovations,
            'categories' => $categories,
            'recentInnovations' => $recentInnovations,
            'stats' => $stats,
            'currentUser' => $currentUser
        ]);
    }
    
    public function dashboard() {
        $this->requireLogin();
        
        $currentUser = $this->getCurrentUser();
        
        if ($currentUser['role'] === 'admin') {
            $this->redirect('/admin');
        }
        
        if ($currentUser['role'] === 'innovator') {
            // Get innovator's innovations
            $innovations = $this->user->getInnovations($currentUser['id']);
            
            // Get messages
            $messages = $this->message->getInbox($currentUser['id'], 1);
            
            // Get unread message count
            $unreadCount = $this->message->getUnreadCount($currentUser['id']);
            
            $this->render('dashboard/innovator', [
                'currentUser' => $currentUser,
                'innovations' => $innovations,
                'messages' => $messages,
                'unreadCount' => $unreadCount
            ]);
        } else {
            // Sponsor dashboard
            $favorites = $this->user->getFavorites($currentUser['id']);
            $messages = $this->message->getInbox($currentUser['id'], 1);
            $sponsorship = new Sponsorship();
            $sponsored = $sponsorship->countBySponsor($currentUser['id']);
            $stats = [
                'favorites' => is_array($favorites) ? count($favorites) : 0,
                'messages' => isset($messages['total']) ? $messages['total'] : 0,
                'sponsored' => $sponsored
            ];
            $this->render('dashboard/sponsor', [
                'currentUser' => $currentUser,
                'stats' => $stats
            ]);
        }
    }
    
    public function about() {
        $this->render('about', [
            'currentUser' => $this->getCurrentUser()
        ]);
    }
    
    public function contact() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validate($data, [
                'name' => 'required|max:255',
                'email' => 'required|email',
                'subject' => 'required|max:255',
                'message' => 'required|min:10'
            ]);
            
            if (empty($errors)) {
                // Here you would typically send an email
                // For now, we'll just set a flash message
                $this->setFlash('success', 'Thank you for your message. We will get back to you soon!');
                $this->redirect('/contact');
            } else {
                $this->render('contact', [
                    'errors' => $errors,
                    'data' => $data,
                    'currentUser' => $this->getCurrentUser()
                ]);
                return;
            }
        }
        
        $this->render('contact', [
            'currentUser' => $this->getCurrentUser()
        ]);
    }
    
    public function search() {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        
        if (empty($query)) {
            $this->redirect('/innovations');
        }
        
        $results = $this->innovation->search($query, $page);
        $categories = $this->category->getActive();
        
        $this->render('search', [
            'results' => $results,
            'categories' => $categories,
            'currentUser' => $this->getCurrentUser()
        ]);
    }
}
?> 