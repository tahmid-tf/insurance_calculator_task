<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Interest Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }

        .results {
            margin-top: 30px;
            background: #e9f7ef;
            padding: 15px;
            border-radius: 6px;
        }

        .results p {
            margin: 8px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h3>Interest Calculator</h3>

    <form method="POST">
        <label>Initial Investment:</label>
        <input type="number" name="initial_investment" value="<?= $_POST['initial_investment'] ?? '' ?>" required/>

        <label>Annual Contribution:</label>
        <input type="number" name="annual_contribution" value="<?= $_POST['annual_contribution'] ?? '' ?>" required/>

        <label>Monthly Contribution:</label>
        <input type="number" name="monthly_contribution" value="<?= $_POST['monthly_contribution'] ?? '' ?>" required/>

        <label>Contribution Timing:</label>
        <select name="contribution_at">
            <option value="begin" <?= (($_POST['contribution_at'] ?? '') == 'begin') ? 'selected' : '' ?>>Beginning
            </option>
            <option value="end" <?= (($_POST['contribution_at'] ?? '') == 'end') ? 'selected' : '' ?>>End</option>
        </select>

        <label>Interest Rate (%):</label>
        <input type="number" name="interest_rate" value="<?= $_POST['interest_rate'] ?? '' ?>" required/>

        <label>Compound:</label>
        <select name="compound">
            <option value="annually" selected>Annually</option>
            <option value="monthly">Monthly</option>
        </select>

        <label>Investment Length:</label>
        <input type="number" name="investment_year" placeholder="Years" value="<?= $_POST['investment_year'] ?? '' ?>"
               required/>
        <input type="number" name="investment_month" placeholder="Months"
               value="<?= $_POST['investment_month'] ?? '' ?>" required/>

        <label>Tax Rate (%):</label>
        <input type="number" name="tax_rate" value="<?= $_POST['tax_rate'] ?? '' ?>" required/>

        <label>Inflation Rate (%):</label>
        <input type="number" name="inflation_rate" value="<?= $_POST['inflation_rate'] ?? '' ?>" required/>

        <button type="submit">Calculate</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $initial = (float) $_POST["initial_investment"];
        $annual = (float) $_POST["annual_contribution"];
        $monthly = (float) $_POST["monthly_contribution"];
        $timing = $_POST["contribution_at"];
        $rate = (float) $_POST["interest_rate"] / 100;
        $compound = $_POST["compound"];
        $taxRate = (float) $_POST["tax_rate"] / 100;
        $inflationRate = (float) $_POST["inflation_rate"] / 100;
        $years = (int) $_POST["investment_year"];
        $months = (int) $_POST["investment_month"];

        $totalMonths = ($years * 12) + $months;
        $balance = $initial;
        $totalAnnualContrib = 0;
        $totalMonthlyContrib = 0;
        $interestFromInitial = 0;
        $interestFromContrib = 0;

        if ($compound === "monthly") {
            $monthlyRate = $rate / 12;

            for ($i = 1; $i <= $totalMonths; $i++) {
                if ($timing === "begin") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }

                $prevBalance = $balance;
                $balance *= (1 + $monthlyRate);
                $interestEarned = $balance - $prevBalance;

                if ($i == 1) {
                    $interestFromInitial += $interestEarned;
                } else {
                    $interestFromContrib += $interestEarned;
                }

                if ($timing === "end") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }

                // Annual contribution every 12 months
                if ($i % 12 == 0) {
                    if ($timing === "begin") {
                        $balance += $annual;
                        $totalAnnualContrib += $annual;
                    }

                    $prevBalance = $balance;
                    $balance *= (1 + $monthlyRate);
                    $interestEarned = $balance - $prevBalance;
                    $interestFromContrib += $interestEarned;

                    if ($timing === "end") {
                        $balance += $annual;
                        $totalAnnualContrib += $annual;
                    }
                }
            }

            $effectiveAnnualRate = pow(1 + $monthlyRate, 12) - 1;
        } else {
            // Annual compounding (original logic)
            $totalYears = $years + ($months / 12);

            for ($i = 1; $i <= floor($totalYears); $i++) {
                if ($timing === "begin") {
                    $balance += $annual;
                    $totalAnnualContrib += $annual;
                }

                $prevBalance = $balance;
                $balance *= (1 + $rate);
                $interestEarned = $balance - $prevBalance;

                if ($i == 1) {
                    $interestFromInitial += $interestEarned;
                } else {
                    $interestFromContrib += $interestEarned;
                }

                if ($timing === "end") {
                    $balance += $annual;
                    $totalAnnualContrib += $annual;
                }
            }

            $remainingMonths = ($totalYears - floor($totalYears)) * 12;
            if ($remainingMonths > 0) {
                if ($timing === "begin") {
                    $monthlyContrib = $monthly * $remainingMonths;
                    $balance += $monthlyContrib;
                    $totalMonthlyContrib += $monthlyContrib;
                }

                $prevBalance = $balance;
                $balance *= pow(1 + $rate, $remainingMonths / 12);
                $interestEarned = $balance - $prevBalance;

                $interestFromContrib += $interestEarned;

                if ($timing === "end") {
                    $monthlyContrib = $monthly * $remainingMonths;
                    $balance += $monthlyContrib;
                    $totalMonthlyContrib += $monthlyContrib;
                }
            }
        }

        $totalContributions = $totalAnnualContrib + $totalMonthlyContrib;
        $totalPrincipal = $initial + $totalContributions;
        $totalInterest = $balance - $totalPrincipal;

        $totalTax = $totalInterest * $taxRate;
        $interestAfterTax = $totalInterest - $totalTax;

        $totalYears = $compound === "monthly" ? $totalMonths / 12 : $years + ($months / 12);
        $buyingPower = ($totalPrincipal + $interestAfterTax) / pow(1 + $inflationRate, $totalYears);

        echo "<div class='results'>";
        echo "<p><strong>Ending Balance:</strong> $" . number_format($balance, 2) . "</p>";
        echo "<p><strong>Total Principal:</strong> $" . number_format($totalPrincipal, 2) . "</p>";
        echo "<p><strong>Total Contributions:</strong> $" . number_format($totalContributions, 2) . "</p>";
        echo "<p><strong>Total Interest:</strong> $" . number_format($totalInterest, 2) . "</p>";
        echo "<p><strong>Interest of Initial Investment:</strong> $" . number_format($interestFromInitial, 2) . "</p>";
        echo "<p><strong>Interest of the Contributions:</strong> $" . number_format($interestFromContrib, 2) . "</p>";
        echo "<p><strong>Total Tax:</strong> $" . number_format($totalTax, 2) . "</p>";
        echo "<p><strong>Total Interest After Tax:</strong> $" . number_format($interestAfterTax, 2) . "</p>";
        echo "<p><strong>Buying Power After Inflation:</strong> $" . number_format($buyingPower, 2) . "</p>";

        if ($compound === "monthly") {
            echo "<p><em>* Interest rate of " . ($_POST['interest_rate']) . "% compounded monthly is equivalent to ";
            echo number_format($effectiveAnnualRate * 100, 3) . "% annually</em></p>";
        }

        echo "</div>";
    }
    ?>


</div>
</body>
</html>
