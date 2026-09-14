<?php
require 'db.php';

function amountInWords(float $number) {
    $no = floor($number);
    $decimal = round($number - $no, 2) * 100;
    
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $str [] = ($number < 21) ? $words[$number].' '. $digits[count($str)] : $words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[count($str)];
        } else {
            $str[] = null;
        }
    }
    $Rupees = implode(' ', array_filter(array_reverse($str)));
    $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return trim($Rupees . $paise) . ' Only';
}

$job_filter = $_GET['job_name'] ?? null;
$customer_filter = $_GET['customer'] ?? null;
$is_consolidated = isset($_GET['consolidated']) && $_GET['consolidated'] == '1';

try {
    if ($job_filter) {
        $where_sql = "WHERE job_name = ?";
        $params[] = $job_filter;
    } elseif ($customer_filter) {
        $where_sql = "WHERE customer_name = ? AND payment_status = 'Pending'";
        $params[] = $customer_filter;
    } else {
        $where_sql = "WHERE payment_status = 'Pending'";
    }

    $stmt = $pdo->prepare("SELECT * FROM works $where_sql ORDER BY job_name ASC, work_date ASC");
    $stmt->execute($params ?? []);
    $works = $stmt->fetchAll();
    
    $grouped_works = [];
    $total_all = 0;
    
    foreach($works as $work) {
        $group_key = $is_consolidated ? ($work['customer_name'] ?: 'Unknown Customer') : ($work['job_name'] ?: 'Uncategorized');
        if (!isset($grouped_works[$group_key])) {
            $grouped_works[$group_key] = [
                'items' => [],
                'subtotal' => 0,
                'customer_name' => $work['customer_name'] ?? ''
            ];
        }
        $grouped_works[$group_key]['items'][] = $work;
        $grouped_works[$group_key]['subtotal'] += $work['amount'];
        $total_all += $work['amount'];
    }
    
} catch (Exception $e) {
    $works = [];
    $grouped_works = [];
    $total_all = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .no-print { margin-bottom: 20px; text-align: center; }
        .print-btn {
            padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px; border-radius: 4px; font-weight: bold;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #000;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            border-top: none;
        }
        .footer-table td {
            border: 1px solid #000;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0; /* This removes the browser's default header (date/title) and footer (URL/page numbers) */
            }
            .no-print { display: none; }
            body { 
                padding: 10mm; /* Re-add margin so content isn't cut off by printer borders */
                font-size: 12px; 
            }
            .invoice-container { width: 100%; max-width: 100%; margin-bottom: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">Print Invoice</button>
        <button onclick="toggleEditMode()" class="print-btn" id="editBtn" style="background: #0284c7;">Enable Edit Mode</button>
        <a href="index.php" class="print-btn">Back to Dashboard</a>
    </div>

    <script>
        let isEditMode = false;
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const containers = document.querySelectorAll('.invoice-container');
            const btn = document.getElementById('editBtn');
            
            containers.forEach(container => {
                container.contentEditable = isEditMode ? "true" : "false";
                container.style.outline = isEditMode ? "2px dashed #0284c7" : "none";
            });
            
            if (isEditMode) {
                btn.textContent = "Disable Edit Mode";
                btn.style.background = "#dc2626";
            } else {
                btn.textContent = "Enable Edit Mode";
                btn.style.background = "#0284c7";
            }
        }
    </script>

    <?php if (count($grouped_works) > 0): ?>
        <?php 
        $job_index = 0;
        $total_jobs = count($grouped_works);
        foreach ($grouped_works as $job_name => $data): 
            $job_index++;
            
            $cust_name = $data['customer_name'] ?: 'Cash';
            $stmt_cust = $pdo->prepare("SELECT * FROM customers WHERE name = ?");
            $stmt_cust->execute([$cust_name]);
            $customer_details = $stmt_cust->fetch();
            
            $cust_address = $customer_details['address'] ?? '';
            $cust_gstin = $customer_details['gstin'] ?? '';
            $cust_phone = $customer_details['phone'] ?? '';
            
            $ids_filter = $_GET['ids'] ?? null;
            $include_others = isset($_GET['include_others']) ? (bool)$_GET['include_others'] : true;
            
            $detailed_items = [];
            $aggregated_amount = 0;
            
            if ($ids_filter) {
                $allowed_ids = explode(',', $ids_filter);
                foreach ($data['items'] as $item) {
                    if (in_array($item['id'], $allowed_ids)) {
                        $detailed_items[] = $item;
                    } else {
                        $aggregated_amount += $item['amount'];
                    }
                }
            } else {
                $detailed_items = $data['items'];
            }
            
            // Build the table rows logically
            $rows = [];
            $total_taxable = 0;
            
            foreach ($detailed_items as $item) {
                $amount = (float)$item['amount'];
                $cgst = $amount * 0.09;
                $sgst = $amount * 0.09;
                $net = $amount + $cgst + $sgst;
                
                $desc = $item['description'];
                if ($is_consolidated && !empty($item['job_name'])) {
                    $desc .= ' (Job: ' . $item['job_name'] . ')';
                }

                $rows[] = [
                    'desc' => $desc,
                    'taxable' => $amount,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'net' => $net
                ];
                $total_taxable += $amount;
            }
            
            if ($include_others && $aggregated_amount > 0) {
                $amount = (float)$aggregated_amount;
                $cgst = $amount * 0.09;
                $sgst = $amount * 0.09;
                $net = $amount + $cgst + $sgst;
                
                $rows[] = [
                    'desc' => 'Other Miscellaneous Works',
                    'taxable' => $amount,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'net' => $net
                ];
                $total_taxable += $amount;
            }
            
            $total_cgst = $total_taxable * 0.09;
            $total_sgst = $total_taxable * 0.09;
            $total_tax = $total_cgst + $total_sgst;
            $grand_total = $total_taxable + $total_tax;
        ?>
        
        <div class="invoice-container" <?= ($job_index < $total_jobs) ? 'style="page-break-after: always; margin-bottom: 50px;"' : '' ?>>
            <table class="invoice-table">
                <tr>
                    <td colspan="11" style="text-align: center; padding: 15px;">
                        <h1 style="margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 0.5px;">OM DIE ENGINEERING</h1>
                        <p style="margin: 5px 0 0; font-size: 14px;">NANDANVAN SOCIETY, MAHAKALI NAGAR, STREET 5/532<br>
                        BHAGVATI PARA, RAJKOT, 360003 , mo.9898334311</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="11" style="padding: 5px 10px; font-weight: bold; position: relative;">
                        <span style="float: left;">Debit Memo</span>
                        <span style="position: absolute; left: 50%; transform: translateX(-50%); font-size: 16px;">TAX INVOICE</span>
                        <span style="float: right;">Original</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="padding: 10px; vertical-align: top; width: 60%;">
                        <b>M/s. : <?= htmlspecialchars($cust_name) ?></b><br>
                        <?php if ($cust_address) echo nl2br(htmlspecialchars($cust_address)) . '<br>'; ?>
                        <?php if ($cust_phone) echo 'Ph: ' . htmlspecialchars($cust_phone) . '<br>'; ?>
                        <br>
                        Place of Supply : 24-Gujarat<br>
                        <b>GSTIN No. :</b> <?= htmlspecialchars($cust_gstin) ?>
                    </td>
                    <td colspan="5" style="padding: 10px; vertical-align: top; width: 40%;">
                        <b>Invoice No. &nbsp;&nbsp;: GT/<?= date('y') ?>/<?= rand(100,999) ?></b><br>
                        <br>
                        <b>Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= date('d/m/Y') ?></b>
                    </td>
                </tr>
                <tr>
                    <th rowspan="2" style="padding: 5px; width: 4%;">Sr.</th>
                    <th rowspan="2" style="padding: 5px; width: 34%;">Product Name</th>
                    <th rowspan="2" style="padding: 5px; width: 8%;">HSN/SAC<br>Code</th>
                    <th rowspan="2" style="padding: 5px; width: 5%;">Qty</th>
                    <th rowspan="2" style="padding: 5px; width: 7%;">Rate</th>
                    <th rowspan="2" style="padding: 5px; width: 6%;">Discount</th>
                    <th rowspan="2" style="padding: 5px; width: 10%;">Taxable<br>Amount</th>
                    <th rowspan="2" style="padding: 5px; width: 5%;">GST<br>%</th>
                    <th colspan="2" style="padding: 5px; text-align: center; width: 12%;">Tax Amount</th>
                    <th rowspan="2" style="padding: 5px; width: 9%;">Net<br>Amount</th>
                </tr>
                <tr>
                    <th style="padding: 2px;">Central</th>
                    <th style="padding: 2px;">State/UT</th>
                </tr>
                
                <?php 
                $sr = 1;
                foreach ($rows as $row): 
                ?>
                <tr>
                    <td style="padding: 5px; text-align: center; border-bottom: none; border-top: none;"><?= $sr++ ?></td>
                    <td style="padding: 5px; border-bottom: none; border-top: none;">
                        <?= htmlspecialchars($row['desc']) ?>
                        <?php if($job_filter): ?><br><small>(Job: <?= htmlspecialchars($job_name) ?>)</small><?php endif; ?>
                    </td>
                    <td style="padding: 5px; text-align: center; border-bottom: none; border-top: none;">999028</td>
                    <td style="padding: 5px; text-align: center; border-bottom: none; border-top: none;">1.000</td>
                    <td style="padding: 5px; text-align: right; border-bottom: none; border-top: none;"><?= number_format($row['taxable'], 2) ?></td>
                    <td style="padding: 5px; border-bottom: none; border-top: none;"></td>
                    <td style="padding: 5px; text-align: right; border-bottom: none; border-top: none;"><?= number_format($row['taxable'], 2) ?></td>
                    <td style="padding: 5px; text-align: center; border-bottom: none; border-top: none;">18.0</td>
                    <td style="padding: 5px; text-align: right; border-bottom: none; border-top: none;"><?= number_format($row['cgst'], 2) ?></td>
                    <td style="padding: 5px; text-align: right; border-bottom: none; border-top: none;"><?= number_format($row['sgst'], 2) ?></td>
                    <td style="padding: 5px; text-align: right; border-bottom: none; border-top: none;"><?= number_format($row['net'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <!-- Spacer Row to fill height -->
                <tr>
                    <td style="height: 150px; border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                    <td style="border-top: none; border-bottom: none;"></td>
                </tr>
                
                <tr>
                    <td colspan="2" style="padding: 5px; font-weight: bold;">GSTIN No.: 24CNGPP2426Q1ZK</td>
                    <td colspan="4" style="padding: 5px; text-align: right; font-weight: bold;">Total</td>
                    <td style="padding: 5px; text-align: right; font-weight: bold;"><?= number_format($total_taxable, 2) ?></td>
                    <td style="padding: 5px;"></td>
                    <td style="padding: 5px; text-align: right; font-weight: bold;"><?= number_format($total_cgst, 2) ?></td>
                    <td style="padding: 5px; text-align: right; font-weight: bold;"><?= number_format($total_sgst, 2) ?></td>
                    <td style="padding: 5px; text-align: right; font-weight: bold;"><?= number_format($grand_total, 2) ?></td>
                </tr>
            </table>
            
            <table class="footer-table">
                <tr>
                    <td style="width: 75%; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px;">
                        <table style="width: 100%; border: none;">
                            <tr><td style="width: 140px; font-weight: bold; border: none; padding: 2px;">Bank Name</td><td style="border: none; padding: 2px;">: THE CO-OPERATIVE BANK OF RAJKOT LTD.</td></tr>
                            <tr><td style="font-weight: bold; border: none; padding: 2px;">Bank A/c. No.</td><td style="border: none; padding: 2px;">: 0010110100000579</td></tr>
                            <tr><td style="font-weight: bold; border: none; padding: 2px;">RTGS/IFSC Code</td><td style="border: none; padding: 2px;">: TCBR0000110 FOR NEFT/RTGS</td></tr>
                        </table>
                    </td>
                    <td style="width: 25%; border-bottom: 1px solid #000;"></td>
                </tr>
                <tr>
                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px;">
                        <b>Total GST :</b> <i><?= amountInWords($total_tax) ?></i>
                    </td>
                    <td style="border-bottom: 1px solid #000; padding: 5px 10px; font-size: 15px; font-weight: bold;">
                        <span style="float: left;">Grand Total</span>
                        <span style="float: right;"><?= number_format($grand_total, 2) ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px;">
                        <b>Bill Amount :</b> <i><?= amountInWords($grand_total) ?></i>
                    </td>
                    <td style="border-bottom: 1px solid #000; padding: 5px; vertical-align: top;">
                        <b>Note :</b>
                    </td>
                </tr>
                <tr>
                    <td style="border-right: 1px solid #000; padding: 5px; font-size: 12px; line-height: 1.5; vertical-align: top;">
                        <b>Terms & Condition :</b><br>
                        <i>1. Goods once sold will not be taken back.<br>
                        2. Interest @18% p.a. will be charged if payment is not made within due date.<br>
                        3. Our risk and responsibility ceases as soon as the goods leave our premises.<br>
                        4. "Subject to 'RAJKOT,' Jurisdiction only. E.&.O.E"</i>
                    </td>
                    <td style="padding: 5px; text-align: right; vertical-align: bottom; font-size: 12px; height: 80px;">
                        For, OM DIE ENGINEERING<br><br><br><br>
                        (Authorised Signatory)
                    </td>
                </tr>
            </table>
        </div>
        
        <?php endforeach; ?>
        
    <?php else: ?>
        <p style="text-align: center; color: #666; font-size: 16px;">No pending payments found for this criteria.</p>
    <?php endif; ?>

</body>
</html>
