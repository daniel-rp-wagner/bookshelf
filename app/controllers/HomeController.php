<?php

class HomeController extends Controller
{
    // Method to display all books
    public function index()
    {
        // Render the view with the list of books by calling renderView() method which is inherited from Controller super class
        $this->renderView('Hallo Welt', 'Title');
    }
}