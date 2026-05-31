<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

$team_members = [];

$stmt = mysqli_prepare($conn, "
    SELECT id, name, position, photo, facebook, twitter, linkedin
    FROM team_members
    WHERE status = 'active'
    ORDER BY display_order ASC, id ASC
    LIMIT 4
");

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $team_members[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- TEAM AREA START (Team - 3) -->
<div class="ltn__team-area pt-115 pb-90">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title white-color---">Team Member</h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php if (!empty($team_members)): ?>
                <?php foreach ($team_members as $row): ?>
                    <?php
                    $name     = trim($row['name'] ?? '');
                    $position = trim($row['position'] ?? '');
                    $photo    = !empty($row['photo'])
                        ? $base_url . 'uploads/team/' . rawurlencode($row['photo'])
                        : $base_url . 'img/team/1.jpg';

                    $facebook = trim($row['facebook'] ?? '');
                    $twitter  = trim($row['twitter'] ?? '');
                    $linkedin = trim($row['linkedin'] ?? '');
                    ?>
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="ltn__team-item">
                            <div class="team-img">
                                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($name ?: 'Team Member') ?>">
                            </div>
                            <div class="team-info">
                                <h6 class="ltn__secondary-color">// <?= htmlspecialchars($position) ?> //</h6>
                                <h4><a href="javascript:void(0)"><?= htmlspecialchars($name) ?></a></h4>
                                <div class="ltn__social-media">
                                    <ul>
                                        <?php if (!empty($facebook)): ?>
                                            <li><a href="<?= htmlspecialchars($facebook) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a></li>
                                        <?php endif; ?>

                                        <?php if (!empty($twitter)): ?>
                                            <li><a href="<?= htmlspecialchars($twitter) ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a></li>
                                        <?php endif; ?>

                                        <?php if (!empty($linkedin)): ?>
                                            <li><a href="<?= htmlspecialchars($linkedin) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin"></i></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No team members available right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- TEAM AREA END -->