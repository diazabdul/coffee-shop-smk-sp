<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}

function generateTransactionID($conn) {
    $sql = "SELECT id_trx FROM transaction ORDER BY id_trx DESC LIMIT 1";
    $result = $conn->query($sql);
    $newID = "TRX001"; // Default ID if table is empty

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastID = $row['id_trx'];
        $number = intval(substr($lastID, 3)) + 1;
        $newID = 'TRX' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    return $newID;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_to_cart'])) {
        $id_menu = $_POST['id_menu'];
        $quantity = $_POST['quantity'];
        
        $menu_sql = "SELECT * FROM menu WHERE id_menu = '$id_menu'";
        $menu_result = $conn->query($menu_sql);
        $menu_item = $menu_result->fetch_assoc();

        $item = [
            'id_menu' => $id_menu,
            'name' => $menu_item['name'],
            'price' => $menu_item['price'],
            'quantity' => $quantity,
            'total' => $menu_item['price'] * $quantity
        ];

        array_push($_SESSION['cart'], $item);
    } elseif (isset($_POST['buy'])) {
        $total = array_sum(array_column($_SESSION['cart'], 'total'));
        $id_trx = generateTransactionID($conn);
        $sql = "INSERT INTO transaction (id_trx, total, date, id_officer, status) VALUES ('$id_trx', $total, NOW(), '', 'Pending')";
        
        if ($conn->query($sql) === TRUE) {
            foreach ($_SESSION['cart'] as $item) {
                $id_menu = $item['id_menu'];
                $quantity = $item['quantity'];
                $total = $item['total'];
                $sql = "INSERT INTO transaction_details (id_trx, id_menu, quantity, total) VALUES ('$id_trx', '$id_menu', $quantity, $total)";
                $conn->query($sql);
            }
            $_SESSION['cart'] = []; // Clear the cart after purchase
            $_SESSION['message'] = "Purchase successful!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

$sql = "SELECT * FROM menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu List</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Menu List</h1>

    <?php if ($_SESSION['message']): ?>
        <p><?= $_SESSION['message'] ?></p>
        <?php $_SESSION['message'] = ''; ?>
    <?php endif; ?>

    <table border="1">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['price'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_menu" value="<?= $row['id_menu'] ?>">
                    <input type="number" name="quantity" value="1" min="1" required>
            </td>
            <td>
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Cart</h2>
    <?php if (!empty($_SESSION['cart'])): ?>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <?php foreach ($_SESSION['cart'] as $item): ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['price'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= $item['total'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <form method="post">
        <button type="submit" name="buy">Buy</button>
    </form>
    <?php else: ?>
    <p>Your cart is empty.</p>
    <?php endif; ?>
</body>
</html>
