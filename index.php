<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Interest Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h3>Interest Calculator</h3>

    <form method="POST">
        <label>Initial Investment ($):</label>
        <input type="number" name="initial_investment" value="<?= $_POST['initial_investment'] ?? '' ?>" required min="0"/>

        <label>Annual Contribution ($):</label>
        <input type="number" name="annual_contribution" value="<?= $_POST['annual_contribution'] ?? 0 ?>" required min="0"/>

        <label>Monthly Contribution ($):</label>
        <input type="number" name="monthly_contribution" value="<?= $_POST['monthly_contribution'] ?? 0 ?>" required min="0"/>

        <label>Contribution Timing:</label>
        <select name="contribution_at">
            <option value="begin" <?= (($_POST['contribution_at'] ?? '') == 'begin') ? 'selected' : '' ?>>Beginning
            </option>
            <option value="end" <?= (($_POST['contribution_at'] ?? '') == 'end') ? 'selected' : '' ?>>End</option>
        </select>

        <label>Interest Rate (%):</label>
        <input type="number" name="interest_rate" value="<?= $_POST['interest_rate'] ?? 0 ?>" required min="0"/>

        <label>Compound:</label>
        <select name="compound">
            <option value="annually" <?= ($_POST['compound'] ?? '') === 'annually' ? 'selected' : '' ?>>Annually
            </option>
            <option value="quarterly" <?= ($_POST['compound'] ?? '') === 'quarterly' ? 'selected' : '' ?>>Quarterly
            </option>
            <option value="monthly" <?= ($_POST['compound'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="daily" <?= ($_POST['compound'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
            <option value="continuous" <?= ($_POST['compound'] ?? '') === 'continuous' ? 'selected' : '' ?>>
                Continuously
            </option>
        </select>


        <label>Investment Length [year : example : 1]:</label>
        <input type="number" name="investment_year" placeholder="Years" value="<?= $_POST['investment_year'] ?? 1 ?>"
               required min="0"/>
        <input type="number" name="investment_month" placeholder="Months"
               value="<?= $_POST['investment_month'] ?? 0 ?>" required min="0"/>

        <label>Tax Rate (%):</label>
        <input type="number" name="tax_rate" value="<?= $_POST['tax_rate'] ?? 0 ?>" required min="0"/>

        <label>Inflation Rate (%):</label>
        <input type="number" name="inflation_rate" value="<?= $_POST['inflation_rate'] ?? 0 ?>" required min="0"/>

        <button type="submit">Calculate</button>
    </form>

    <?php
    include_once "./main.php";
    ?>


</div>
</body>
</html>
