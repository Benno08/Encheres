<?php

// Page d'accueil
$app->get('/[index.html]', '\App\Controller\MainController:startPage')->setName('HOME_PAGE');

// Page présentant les enchères
$app->post('/encheres', '\App\Controller\MainController:joinSession')->setName('ENCHERES');

// Soumission d'une enchère
$app->post('/encherir', '\App\Controller\MainController:postEnchere')->setName('ENCHERIR');

// Page présentant les enchères
$app->post('/capitaljoueur', '\App\Controller\MainController:getCapitalJoueur')->setName('CAPITAL_JOUEUR');