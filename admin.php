<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

function generateMenuID($conn) {
    $sql = "SELECT id_menu FROM menu ORDER BY id_menu DESC LIMIT 1";
    $result = $conn->query($sql);
    $newID = "MN001"; // Default ID if table is empty

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastID = $row['id_menu'];
        $number = intval(substr($lastID, 2)) + 1;
        $newID = 'MN' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    return $newID;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        $id_menu = generateMenuID($conn);
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $sql = "INSERT INTO menu (id_menu, name, price, stock) VALUES ('$id_menu', '$name', $price, $stock)";
        $conn->query($sql);
    } elseif (isset($_POST['update'])) {
        $id_menu = $_POST['id_menu'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $sql = "UPDATE menu SET name='$name', price=$price, stock=$stock WHERE id_menu='$id_menu'";
        $conn->query($sql);
    } elseif (isset($_POST['delete'])) {
        $id_menu = $_POST['id_menu'];
        $sql = "DELETE FROM menu WHERE id_menu='$id_menu'";
        $conn->query($sql);
    } elseif (isset($_POST['update_status'])) {
        $id_trx = $_POST['id_trx'];
        $status = $_POST['status'];


        $username = $_SESSION['username'];        
        $currentOfficerSql = "Select id_officer From Login Where username ='$username'";
        $resultOfficer = $conn->query($currentOfficerSql);
        $officerRow = $resultOfficer->fetch_assoc();
        $idOfficer = $officerRow["id_officer"];

        $sql = "UPDATE transaction SET status='$status', id_officer='$idOfficer' WHERE id_trx='$id_trx'";
        $conn->query($sql);
    }
}

$menu_sql = "SELECT * FROM menu";
$menu_result = $conn->query($menu_sql);

$transaction_sql = "SELECT * FROM transaction";
$transaction_result = $conn->query($transaction_sql);

$transaction_details_sql = "SELECT * FROM transaction_details";
$transaction_details_result = $conn->query($transaction_details_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Page</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Admin Page</h1>
    <a href="logout.php">Logout</a>
    <h2>Manage Menu</h2>
    <form method="post">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Price: <input type="number" name="price" required></label><br>
        <label>Stock: <input type="number" name="stock" required></label><br>
        <button type="submit" name="create">Create</button>
    </form>

    <h2>Menu List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $menu_result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_menu'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['price'] ?></td>
            <td><?= $row['stock'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_menu" value="<?= $row['id_menu'] ?>">
                    <input type="text" name="name" value="<?= $row['name'] ?>" required>
                    <input type="number" name="price" value="<?= $row['price'] ?>" required>
                    <input type="number" name="stock" value="<?= $row['stock'] ?>" required>
                    <button type="submit" name="update">Update</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_menu" value="<?= $row['id_menu'] ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Transaction List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Total</th>
            <th>Date</th>
            <th>Officer</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $transaction_result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_trx'] ?></td>
            <td><?= $row['total'] ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= $row['id_officer'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_trx" value="<?= $row['id_trx'] ?>">
                    <select name="status">
                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Succes" <?= $row['status'] == 'Succes' ? 'selected' : '' ?>>Success</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Transaction Details</h2>
    <table border="1">
        <tr>
            <th>Transaction ID</th>
            <th>Menu ID</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <?php while($row = $transaction_details_result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_trx'] ?></td>
            <td><?= $row['id_menu'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $row['total'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
