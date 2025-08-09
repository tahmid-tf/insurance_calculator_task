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
    $monthlySchedule = [];

    if ($compound === "continuous") {
        $balance = $initial * exp($rate * $totalYears);
        $interestFromInitial = $balance - $initial;
        $totalAnnualContrib = $annual * $years;
        $totalMonthlyContrib = $monthly * $totalMonths;
        $totalContributions = $totalAnnualContrib + $totalMonthlyContrib;
        $balance += $totalContributions;
        $interestFromContrib = 0;
    } elseif ($compound === "monthly") {
        $ratePerPeriod = $rate / 12;
        for ($i = 1; $i <= $totalMonths; $i++) {
            $deposit = 0;

            // Monthly deposit at beginning
            if ($timing === "begin") {
                $balance += $monthly;
                $deposit += $monthly;
                $totalMonthlyContrib += $monthly;
            }

            // Annual deposit at beginning
            if ($i % 12 == 0 && $timing === "begin") {
                $balance += $annual;
                $deposit += $annual;
                $totalAnnualContrib += $annual;
            }

            // Apply interest after deposit
            $prevBalance = $balance;
            $balance *= (1 + $ratePerPeriod);
            $interestEarned = $balance - $prevBalance;

            if ($i == 1) {
                $interestFromInitial += $interestEarned;
            } else {
                $interestFromContrib += $interestEarned;
            }

            // Monthly deposit at end
            if ($timing === "end") {
                $balance += $monthly;
                $deposit += $monthly;
                $totalMonthlyContrib += $monthly;
            }

            // Annual deposit at end
            if ($i % 12 == 0 && $timing === "end") {
                $balance += $annual;
                $deposit += $annual;
                $totalAnnualContrib += $annual;
            }

            $monthlySchedule[] = [
                'month' => $i,
                'deposit' => $deposit,
                'interest' => $interestEarned,
                'balance' => $balance
            ];
        }
    } else {
        switch ($compound) {
            case "daily":
                $periods = round($totalYears * 365);
                $ratePerPeriod = $rate / 365;
                break;
            case "quarterly":
                $periods = round($totalYears * 4);
                $ratePerPeriod = $rate / 4;
                break;
            default:
                $periods = floor($totalYears);
                $ratePerPeriod = $rate;
        }

        for ($i = 1; $i <= $periods; $i++) {
            if ($monthly > 0 && in_array($compound, ["daily", "quarterly"])) {
                if ($timing === "begin") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }
            }

            $isAnnualPeriod = false;
            if ($compound === "daily" && $i % 365 === 0) $isAnnualPeriod = true;
            if ($compound === "quarterly" && $i % 4 === 0) $isAnnualPeriod = true;
            if ($compound === "annually") $isAnnualPeriod = true;

            if ($annual > 0 && $isAnnualPeriod) {
                if ($timing === "begin") {
                    $balance += $annual;
                    $totalAnnualContrib += $annual;
                }
            }

            $prevBalance = $balance;
            $balance *= (1 + $ratePerPeriod);
            $interestEarned = $balance - $prevBalance;

            if ($i == 1) {
                $interestFromInitial += $interestEarned;
            } else {
                $interestFromContrib += $interestEarned;
            }

            if ($monthly > 0 && in_array($compound, ["daily", "quarterly"])) {
                if ($timing === "end") {
                    $balance += $monthly;
                    $totalMonthlyContrib += $monthly;
                }
            }

            if ($annual > 0 && $isAnnualPeriod) {
                if ($timing === "end") {
                    $balance += $annual;
                    $totalAnnualContrib += $annual;
                }
            }
        }
    }

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

    if ($compound === "continuous") {
        $effectiveAnnualRate = exp($rate) - 1;
        echo "<p><em>* Interest rate of " . ($_POST['interest_rate']) . "% compounded continuously is equivalent to ";
        echo number_format($effectiveAnnualRate * 100, 3) . "% annually</em></p>";
    } elseif ($compound !== "annually") {
        $periodsPerYear = $compound === "monthly" ? 12 :
            ($compound === "daily" ? 365 :
                ($compound === "quarterly" ? 4 : 1));
        $effectiveAnnualRate = pow(1 + $ratePerPeriod, $periodsPerYear) - 1;
        echo "<p><em>* Interest rate of " . ($_POST['interest_rate']) . "% compounded $compound is equivalent to ";
        echo number_format($effectiveAnnualRate * 100, 3) . "% annually</em></p>";
    }

    echo "</div>";

    // Monthly accumulation table
    if ($compound === "monthly") {
        echo "<h4>Accumulation Schedule</h4>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width:100%; background:#fff;'>";
        echo "<tr style='background:#e0e0e0;'><th>Month</th><th>Deposit</th><th>Interest</th><th>Ending Balance</th></tr>";

        foreach ($monthlySchedule as $row) {
            echo "<tr>";
            echo "<td>" . $row['month'] . "</td>";
            echo "<td>$" . number_format($row['deposit'], 2) . "</td>";
            echo "<td>$" . number_format($row['interest'], 2) . "</td>";
            echo "<td>$" . number_format($row['balance'], 2) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}
?>