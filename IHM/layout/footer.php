<?php
$estConnecte = in_array($_SESSION['role'] ?? null, ['client', 'fournisseur'], true);
?>
<?php if ($estConnecte): ?>
            </main>
        </div>
    </div>
<?php else: ?>
    </main>
    <footer class="site-footer">
        <p>© <?= date('Y') ?> <?= e(APP_NAME) ?> — ENSA Tétouan</p>
    </footer>
<?php endif; ?>

<script src="<?= url('assets/js/app.js') ?>" defer></script>
</body>
</html>
