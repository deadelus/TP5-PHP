<?php include('header.php'); ?>

<?php
$poll = false;
if (isset($_GET['id'])) {
    $query = $pdo->prepare('SELECT * FROM polls WHERE id=:id');
    $query->bindParam(':id',$_GET['id']);
    $query->execute();

    if ($query->rowCount()) {
        $poll = $query->fetch();
    }
}

if (!$poll) {
?>
<h2>Sondage</h2>

<div class="alert alert-danger">
    Sondage non trouvé.
</div>
<?php
} else {

$userAnswered = false;
$answers = null;

if ($currentUser) {
    $query = $pdo->prepare('SELECT * FROM answers WHERE poll_id=:pollid AND user_id=:userid');
    $query->bindParam(':pollid',$poll['id']);
    $query->bindParam(':userid',$currentUser['id']);
    $query->execute();

    if ($query->rowCount()) {
        $userAnswered = true;
    } else if (isset($_POST['answer'])){
        if ($_SERVER['REQUEST_METHOD']=='POST' && !empty($_POST['answer']) &&
            $_POST['answer']=='1' || $_POST['answer']=='2' || $_POST['answer']=='3') {

                $req = $pdo->prepare('INSERT INTO answers (user_id, poll_id, answer)
                    VALUES (:id,:pollid, :aw)');
                $req->bindParam(':id',$currentUser['id']);
                $req->bindParam(':pollid',$poll['id']);
                $req->bindParam(':aw',$_POST['answer']);
                $req->execute();

                $userAnswered = true;
            }
    }

    if ($userAnswered) {
        $answers = array();
        foreach (array(1,2,3) as $answer) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as nb FROM answers WHERE 
                poll_id=:pollid AND answer=:asw');

            $stmt->bindParam(':pollid',$poll['id']);
            $stmt->bindParam(':asw',$answer);
            $stmt->execute();
            $query = $stmt->fetch();
            $answers[$answer] = $query['nb'];
        }
        $total = array_sum($answers);
    }
}

?>

<h2><?php echo htmlspecialchars($poll['question']); ?></h2>

<form method="post">

<?php foreach (array(1,2,3) as $answer) { ?>

<?php if (!$poll['answer'.$answer]) continue; ?>

<h3>
    <label>
    <?php if ($currentUser && !$userAnswered) { ?>
    <input type="radio" name="answer" value="<?php echo $answer; ?>" />
    <?php } ?>
    <?php echo htmlspecialchars($poll['answer'.$answer]); ?>
    </label>
</h3>

<?php
if ($answers) {
$pct = round($answers[$answer]*100/$total);

?>

<div class="progress">
<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $pct; ?>%;">
  <?php echo $pct; ?>%
  </div>
</div>

<?php } ?>
<?php } ?>

<?php if ($currentUser) { 
    if (!$userAnswered) { 
?>
    <input class="btn btn-success pull-right" type="submit" value="Participer!" />
<?php
}
} else {
?>
<div class="alert alert-warning">
Vous devez être identifié pour participer!
</div>
<?php
} 
?>
</form>

<?php } ?>

<?php include('footer.php'); ?>
