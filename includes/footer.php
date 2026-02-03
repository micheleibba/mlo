    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 small">
                        &copy; <?= date('Y') ?> Maria Laura Orrù. Tutti i diritti riservati.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= $basePath ?? '' ?>privacy.php" class="text-light text-decoration-none small me-3">
                        <i class="bi bi-shield-check me-1"></i>Privacy
                    </a>
                    <a href="mailto:info@marialauraorru.it" class="text-light text-decoration-none small">
                        <i class="bi bi-envelope me-1"></i>Contatti
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($extraScripts)): ?>
    <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
