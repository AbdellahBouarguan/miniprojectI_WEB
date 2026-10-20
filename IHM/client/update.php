<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <h1><?= e($title) ?></h1>

    <?php
    $isEdit = true;
    $cancelUrl = url('Client/one/' . (int) $client['id_client']);
    require ROOT . 'IHM/client/_form.php';
    ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
