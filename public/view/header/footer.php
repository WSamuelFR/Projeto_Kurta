    </main>

    <footer class="main-footer glass-nav">
        <p>&copy; <?= date("Y") ?> feel.it - Todos os direitos reservados. Emoções sem limites.</p>
    </footer>

    <!-- Scripts injetáveis globais da Engine Compartilhada -->
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/comments.js"></script>
    
    <!-- Scripts injetáveis configurados via View Base -->
    <?php if (isset($extraScript)): ?>
        <script src="<?= $extraScript ?>"></script>
    <?php endif; ?>
</body>
</html>
