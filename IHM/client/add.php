<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <h1><?= e($title) ?></h1>

    <?php
    $isEdit = false;
    $cancelUrl = url('Client/all');
    require ROOT . 'IHM/client/_form.php';
    ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
//pourquoi le e() yak?
/*Ici, ce n’est pas indispensable : 'Gestion des clients'
 est un texte fixe, sans caractère dangereux. 
 On le fait par habitude : la règle est d’échapper tout ce qu’on affiche, 
 sans se demander d’où ça vient. Cela coûte presque rien, et ça protège 
 le jour où le titre deviendra dynamique,
 par exemple $title = 'Client ' . $client['nom'].*/