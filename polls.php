<?php include('header.php'); ?>

<h2>Sondages</h2>

<ul class="list-group">
<?php
$stmt = $pdo->prepare('SELECT * FROM polls');
$stmt->execute();
$query = $stmt->fetchAll();

foreach ($query as $poll) {
?>
<li class="list-group-item">
    <strong><?php echo htmlspecialchars($poll['question']); ?></strong>
    <a href="poll.php?id=<?php echo $poll['id']; ?>" class="btn btn-xs btn-success pull-right">
        Voir &raquo;
    </a>
</li>
<?php
}
?>
</ul>

<?php include('footer.php'); ?>
