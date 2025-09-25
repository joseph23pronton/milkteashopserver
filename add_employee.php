<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="backend/add_employee_method.php" method="post">
                    <div class="form-group">
                        <label for="fname">Employee First Name</label>
                        <input type="text" class="form-control" id="fname" name="fname" required>
                    </div>
                    <div class="form-group">
                        <label for="lname">Employee Last Name</label>
                        <input type="text" class="form-control" id="lname" name="lname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Employee Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Temporary Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Employee Role</label>
                        <select name="role" class="form-control" id="role" required>
                            <option value="cashier">Cashier</option>
                            <option value="encoder">Encoder</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="branch_id">Employee Branch Assignment</label>
                        <select name="branch_id" id="branch_id" class="form-control">
                            <?php
                            $mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";
                            $sql = "SELECT id, name, city FROM branches";
                            $result = $mysqli->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value=\"{$row['id']}\">" . htmlspecialchars($row['name']) . ", " . htmlspecialchars($row['city']) . "</option>";
                                }
                            } else {
                                echo "<option>No branches available</option>";
                            }
                            $mysqli->close();
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>