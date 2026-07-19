<section class="admin-hero panel">
    <span class="eyebrow">Back-office AfiaZone</span>
    <h1>Centre de controle marketplace.</h1>
    <p>Le socle MVC est operationnel. Le back-office recevra ensuite les files KYC, produits, ordonnances et commandes.</p>

    <div class="dashboard-grid">
        <?php foreach ($stats as $stat): ?>
            <div class="dashboard-card">
                <span><?= e($stat['label']) ?></span>
                <strong><?= e($stat['value']) ?></strong>
                <small><?= e($stat['hint']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <span>Files a venir</span>
        <h2>Priorites administratives</h2>
        <a href="<?= e(url('health-check')) ?>">Health</a>
    </div>

    <div class="admin-list">
        <?php foreach ($queues as $name => $description): ?>
            <article>
                <strong><?= e($name) ?></strong>
                <span><?= e($description) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>