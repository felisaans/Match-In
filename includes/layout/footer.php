        </div>
    </main>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (!empty($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= asset('js/' . $script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
