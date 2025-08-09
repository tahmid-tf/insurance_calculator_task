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
            <option value="annually" <?= ($_POST['compound'] ?? '') === 'annually' ? 'selected' : '' ?>>Annually</option>
            <option value="quarterly" <?= ($_POST['compound'] ?? '') === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
            <option value="monthly" <?= ($_POST['compound'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="daily" <?= ($_POST['compound'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
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
        $totalYears = $years + ($months / 12);
        $balance = $initial;
        $totalAnnualContrib = 0;
        $totalMonthlyContrib = 0;
        $interestFromInitial = 0;
        $interestFromContrib = 0;

        // Determine compounding frequency
        switch ($compound) {
            case "monthly":
                $periods = $totalMonths;
                $ratePerPeriod = $rate / 12;
                break;
            case "daily":
                $periods = round($totalYears * 365);
                $ratePerPeriod = $rate / 365;
                break;
            default: // annually
                $periods = floor($totalYears);
                $ratePerPeriod = $rate;
        }

        for ($i = 1; $i <= $periods; $i++) {
            // Monthly contributions (only for monthly/daily compounding)
            if (in_array($compound, ["monthly", "daily"]) && $monthly > 0) {
                if ($timing === "begin") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }
            }

            // Annual contributions (every 12 months or 365 days)
            if ($annual > 0) {
                $isAnnualPeriod = false;
                if ($compound === "monthly" && $i % 12 === 0) $isAnnualPeriod = true;
                if ($compound === "daily" && $i % 365 === 0) $isAnnualPeriod = true;
                if ($compound === "annually") $isAnnualPeriod = true;

                if ($isAnnualPeriod) {
                    if ($timing === "begin") {
                        $balance += $annual;
                        $totalAnnualContrib += $annual;
                    }
                }
            }

            // Apply interest
            $prevBalance = $balance;
            $balance *= (1 + $ratePerPeriod);
            $interestEarned = $balance - $prevBalance;

            if ($i == 1) {
                $interestFromInitial += $interestEarned;
            } else {
                $interestFromContrib += $interestEarned;
            }

            // Contributions after interest
            if (in_array($compound, ["monthly", "daily"]) && $monthly > 0) {
                if ($timing === "end") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }
            }

            if ($annual > 0 && isset($isAnnualPeriod) && $isAnnualPeriod) {
                if ($timing === "end") {
                    $balance += $annual;
                    $totalAnnualContrib += $annual;
                }
            }
        }

        // Final calculations
        $totalContributions = $totalAnnualContrib + $totalMonthlyContrib;
        $totalPrincipal = $initial + $totalContributions;
        $totalInterest = $balance - $totalPrincipal;
        $totalTax = $totalInterest * $taxRate;
        $interestAfterTax = $totalInterest - $totalTax;
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

        // Effective annual rate display
        if ($compound !== "annually") {
            $periodsPerYear = $compound === "monthly" ? 12 : ($compound === "daily" ? 365 : 1);
            $effectiveAnnualRate = pow(1 + $ratePerPeriod, $periodsPerYear) - 1;
            echo "<p><em>* Interest rate of " . ($_POST['interest_rate']) . "% compounded $compound is equivalent to ";
            echo number_format($effectiveAnnualRate * 100, 3) . "% annually</em></p>";
        }

        echo "</div>";
    }
    ?>


</div>
</body>
</html>
