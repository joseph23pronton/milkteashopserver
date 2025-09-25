<?php
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        ?>
        <form action="backend/edit_employee_method.php" method="post">
            <input type="hidden" name="id" value="<?php echo $employee_id; ?>">
            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($employee['fname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($employee['lname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Temporary Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password">
            </div>
            <div class="form-group">
                        <label for="password">Employee Role</label>
                        <select name="role" class="form-control" id="role" required>
                            <option value="cashier" <?php echo $employee['role'] == 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                            <option value="encoder" <?php echo $employee['role'] == 'encoder' ? 'selected' : ''; ?>>Encoder</option>
                        </select>
            </div>
            <div class="form-group">
                <label for="branch_id">Branch</label>
                <select name="branch_id" class="form-control">
                    <?php
                    $branches = $mysqli->query("SELECT id, name, city FROM branches");
                    while ($branch = $branches->fetch_assoc()) {
                        $selected = $branch['id'] == $employee['branch_id'] ? 'selected' : '';
                        echo "<option value=\"{$branch['id']}\" $selected>{$branch['name']} ({$branch['city']})</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <?php
    } else {
        echo "<p>Employee not found.</p>";
    }
} else {
    echo "<p>No ID provided.</p>";
}
?>