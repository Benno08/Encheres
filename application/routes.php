<?php

// Page d'accueil
$app->get('/[index.html]', '\App\Controller\MainController:startPage')->setName('HOME_PAGE');

// Page présentant les enchères
$app->post('/encheres', '\App\Controller\MainController:joinSession')->setName('ENCHERES');

// Soumission d'une enchère
$app->post('/encherir', '\App\Controller\MainController:postEnchere')->setName('ENCHERIR');