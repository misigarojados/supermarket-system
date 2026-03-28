
<?php
$sum = ""; // initialize variable
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];

    $sum = ($num1 + $num2)/2;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sum Calculator</title>
</head>
<body>
<form action="sum.php" method="post">
    <h2>Simple Sum Calculator</h2>

    Num1: <input type="number" name="num1"><br><br>
    Num2: <input type="number" name="num2"><br><br>

    <button type="submit">Calculate</button><br><br>

    <label>Sum: <?php echo $sum; ?></label>
</form>
</body>
</html>
