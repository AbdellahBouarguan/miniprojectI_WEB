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
