<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/countries.php';
$message='';
$error='';
$qr_upload_dir=__DIR__.'/../assets/images/qr/';
$logo_upload_dir=__DIR__.'/../assets/images/';
$qr_prefix='assets/images/qr/';
$logo_prefix='assets/images/';

if(!is_dir($qr_upload_dir)) mkdir($qr_upload_dir,0755,true);
if(!is_dir($logo_upload_dir)) mkdir($logo_upload_dir,0755,true);

if($_SERVER['REQUEST_METHOD']==='POST'){
    // Your existing POST logic remains unchanged
    $action=$_POST['action']??'';
    try{
        $name=trim($_POST['name']??'');
        $wallet_address=trim($_POST['wallet_address']??'');
        $status=(int)($_POST['status']??1);
        $withdrawal_fee=(float)($_POST['withdrawal_fee']??0);
        $currency = trim($_POST['currency'] ?? 'USD');
        $conversion_rate = (float)($_POST['conversion_rate'] ?? 1);
        $active_country = trim($_POST['active_country'] ?? '');
        $min_withdraw = (float)($_POST['min_withdraw'] ?? 0);
        $crypto=(int)($_POST['crypto']??0);
        $type=$_POST['type']??null;
        $network=trim($_POST['network']??'');
        $account_name=trim($_POST['account_name']??'');
        $account_number=trim($_POST['account_number']??'');
        $qr_image_path=$_POST['current_qr_image']??'';
        $logo_path=$_POST['current_logo']??'';

        if(empty($name)){
            throw new Exception("Payment method name required");
        }

        // ... (Your file upload logic for QR and Logo remains the same) ...

        $data = [
            $name, $wallet_address, $qr_image_path, $logo_path, $crypto, $type,
            $network, $account_name, $account_number, $currency, $conversion_rate,
            $active_country, $min_withdraw, $status, $withdrawal_fee
        ];

        if($action==="add"){
            $stmt=$pdo->prepare("INSERT INTO payment_methods 
                (name,wallet_address,qr_image,image,crypto,type,network,account_name,account_number,currency,conversion_rate,active_country,min_withdraw,status,withdrawal_fee)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute($data);
            $message="Payment method added successfully";
        }

        if($action==="edit"){
            $id=(int)$_POST['id'];
            $data[]=$id;
            $stmt=$pdo->prepare("UPDATE payment_methods SET 
                name=?, wallet_address=?, qr_image=?, image=?, crypto=?, type=?, 
                network=?, account_name=?, account_number=?, currency=?, 
                conversion_rate=?, active_country=?, min_withdraw=?, status=?, withdrawal_fee=? 
                WHERE id=?");
            $stmt->execute($data);
            $message="Payment method updated";
        }
    }catch(Exception $e){
        $error=$e->getMessage();
    }
}

// Delete logic remains the same
if(isset($_POST['action']) && $_POST['action']=="delete"){
    // ... your delete code ...
}

$stmt=$pdo->query("SELECT * FROM payment_methods ORDER BY id DESC");
$methods=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
<h1 style="text-align:center;margin:2.5rem 0 2rem;">Manage Payment Methods</h1>

<?php if($message): ?> ... <?php endif; ?>
<?php if($error): ?> ... <?php endif; ?>

<!-- ==================== ADD FORM ==================== -->
<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:2rem;margin-bottom:3rem;max-width:900px;margin-left:auto;margin-right:auto;">
    <h2 style="margin-bottom:1.8rem;text-align:center;">Add Payment Method</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <div style="margin-bottom:1.4rem;">
            <label>Method Name</label>
            <input type="text" name="name" required style="width:100%;padding:0.8rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Crypto?</label>
            <select name="crypto" id="cryptoSelect" style="width:100%;padding:0.8rem;">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>
        </div>

        <div style="margin-bottom:1.4rem;" id="walletSection">
            <label>Wallet Address</label>
            <input type="text" name="wallet_address" style="width:100%;padding:0.8rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Type</label>
            <select name="type" id="typeSelect" style="width:100%;padding:0.8rem;">
                <option value="bank">Bank</option>
                <option value="momo">MOMO</option>
                <option value="paystack">Paystack</option>
            </select>
        </div>

        <!-- Bank/MOMO Fields -->
        <div id="bankFields">
            <div style="margin-bottom:1.4rem;">
                <label>Network / Bank</label>
                <input type="text" name="network" style="width:100%;padding:0.8rem;">
            </div>
            <div style="margin-bottom:1.4rem;">
                <label>Account Name</label>
                <input type="text" name="account_name" style="width:100%;padding:0.8rem;">
            </div>
            <div style="margin-bottom:1.4rem;">
                <label>Account Number</label>
                <input type="text" name="account_number" style="width:100%;padding:0.8rem;">
            </div>
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Logo</label>
            <input type="file" name="logo">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>QR Code</label>
            <input type="file" name="qr_image">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Withdrawal Fee</label>
            <input type="number" step="0.01" name="withdrawal_fee" value="0" style="width:100%;padding:0.8rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Currency</label>
            <input type="text" name="currency" value="USD" style="width:100%;padding:0.8rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Conversion Rate</label>
            <input type="number" step="0.00000001" name="conversion_rate" id="rateInput" value="1" style="width:100%;padding:0.8rem;">
            <div style="margin-top:6px;font-size:13px;color:#9ca3af;">
                Preview: <span id="conversionPreview">1 USD = 1</span>
            </div>
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Active Country (optional)</label>
            <select name="active_country" style="width:100%;padding:0.8rem;">
                <option value="">All Countries</option>
                <?php foreach($countries as $country): ?>
                    <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:1.4rem;">
            <label>Minimum Withdrawal</label>
            <input type="number" step="0.00000001" name="min_withdraw" value="0" style="width:100%;padding:0.8rem;">
        </div>

        <div style="margin-bottom:2rem;">
            <label>Status</label>
            <select name="status" style="width:100%;padding:0.8rem;">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn" style="width:100%;padding:1rem;">Add Payment Method</button>
    </form>
</div>

<!-- ==================== EDIT MODAL (Updated) ==================== -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);align-items:center;justify-content:center;z-index:9999;overflow-y:auto;padding:20px;">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;width:90%;max-width:800px;padding:2rem;position:relative;max-height:90vh;overflow-y:auto;">
        <button onclick="closeEditModal()" style="position:absolute;right:15px;top:10px;font-size:22px;background:none;border:none;color:white;cursor:pointer">×</button>
        <h2 style="text-align:center;margin-bottom:1.5rem;">Edit Payment Method</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="current_qr_image" id="edit_current_qr">
            <input type="hidden" name="current_logo" id="edit_current_logo">

            <div style="margin-bottom:1.4rem;">
                <label>Method Name</label>
                <input type="text" name="name" id="edit_name" style="width:100%;padding:0.8rem;" required>
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Crypto?</label>
                <select name="crypto" id="edit_crypto" style="width:100%;padding:0.8rem;">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div style="margin-bottom:1.4rem;" id="editWalletSection">
                <label>Wallet Address</label>
                <input type="text" name="wallet_address" id="edit_wallet" style="width:100%;padding:0.8rem;">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Type</label>
                <select name="type" id="edit_type" style="width:100%;padding:0.8rem;">
                    <option value="bank">Bank</option>
                    <option value="momo">MOMO</option>
                    <option value="paystack">Paystack</option>
                </select>
            </div>

            <!-- Bank/MOMO Fields -->
            <div id="editBankSection">
                <div style="margin-bottom:1.4rem;">
                    <label>Network / Bank</label>
                    <input type="text" name="network" id="edit_network" style="width:100%;padding:0.8rem;">
                </div>
                <div style="margin-bottom:1.4rem;">
                    <label>Account Name</label>
                    <input type="text" name="account_name" id="edit_account_name" style="width:100%;padding:0.8rem;">
                </div>
                <div style="margin-bottom:1.4rem;">
                    <label>Account Number</label>
                    <input type="text" name="account_number" id="edit_account_number" style="width:100%;padding:0.8rem;">
                </div>
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Current Logo</label>
                <div id="edit_logo_preview" style="margin-bottom:8px;"></div>
                <input type="file" name="logo">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Current QR Code</label>
                <div id="edit_qr_preview" style="margin-bottom:8px;"></div>
                <input type="file" name="qr_image">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Withdrawal Fee</label>
                <input type="number" step="0.01" name="withdrawal_fee" id="edit_fee" style="width:100%;padding:0.8rem;">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Currency</label>
                <input type="text" name="currency" id="edit_currency" style="width:100%;padding:0.8rem;">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Conversion Rate</label>
                <input type="number" step="0.00000001" name="conversion_rate" id="edit_rate" style="width:100%;padding:0.8rem;">
                <div style="margin-top:6px;font-size:13px;color:#9ca3af;">
                    Preview: <span id="editConversionPreview">1 USD = 1</span>
                </div>
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Active Country (optional)</label>
                <select name="active_country" id="edit_country" style="width:100%;padding:0.8rem;">
                    <option value="">All Countries</option>
                    <?php foreach($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Minimum Withdrawal</label>
                <input type="number" step="0.00000001" name="min_withdraw" id="edit_min_withdraw" style="width:100%;padding:0.8rem;">
            </div>

            <div style="margin-bottom:1.4rem;">
                <label>Status</label>
                <select name="status" id="edit_status" style="width:100%;padding:0.8rem;">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width:100%;padding:1rem;">Save Changes</button>
        </form>
    </div>
</div>
</main>

<script>
// ==================== FIELD TOGGLE LOGIC ====================

function toggleAddFields() {
    const type = document.getElementById('typeSelect').value;
    const isCrypto = document.getElementById('cryptoSelect').value == '1';

    const walletSection = document.getElementById('walletSection');
    const bankFields = document.getElementById('bankFields');

    if (type === 'paystack') {
        walletSection.style.display = 'none';
        bankFields.style.display = 'none';
    } else {
        walletSection.style.display = isCrypto ? 'block' : 'none';
        bankFields.style.display = isCrypto ? 'none' : 'block';
    }
}

function toggleEditFields() {
    const type = document.getElementById('edit_type').value;
    const isCrypto = document.getElementById('edit_crypto').value == '1';

    const editWallet = document.getElementById('editWalletSection');
    const editBank = document.getElementById('editBankSection');

    if (type === 'paystack') {
        editWallet.style.display = 'none';
        editBank.style.display = 'none';
    } else {
        editWallet.style.display = isCrypto ? 'block' : 'none';
        editBank.style.display = isCrypto ? 'none' : 'block';
    }
}

// Event Listeners
document.getElementById('typeSelect').addEventListener('change', toggleAddFields);
document.getElementById('cryptoSelect').addEventListener('change', toggleAddFields);

document.getElementById('edit_type').addEventListener('change', toggleEditFields);
document.getElementById('edit_crypto').addEventListener('change', toggleEditFields);

// Open Edit Modal
function openEditModal(m) {
    document.getElementById("editModal").style.display = "flex";
    document.getElementById("edit_id").value = m.id;
    document.getElementById("edit_name").value = m.name;
    document.getElementById("edit_wallet").value = m.wallet_address || "";
    document.getElementById("edit_crypto").value = m.crypto;
    document.getElementById("edit_type").value = m.type || "bank";
    document.getElementById("edit_network").value = m.network || "";
    document.getElementById("edit_account_name").value = m.account_name || "";
    document.getElementById("edit_account_number").value = m.account_number || "";
    document.getElementById("edit_fee").value = m.withdrawal_fee || "0";
    document.getElementById("edit_currency").value = m.currency || "USD";
    document.getElementById("edit_rate").value = m.conversion_rate || 1;
    document.getElementById("edit_country").value = m.active_country || "";
    document.getElementById("edit_min_withdraw").value = m.min_withdraw || 0;
    document.getElementById("edit_status").value = m.status;

    document.getElementById("edit_current_qr").value = m.qr_image || "";
    document.getElementById("edit_current_logo").value = m.image || "";

    document.getElementById("edit_logo_preview").innerHTML = m.image ? `<img src="../${m.image}" style="max-width:80px">` : "No logo";
    document.getElementById("edit_qr_preview").innerHTML = m.qr_image ? `<img src="../${m.qr_image}" style="max-width:80px">` : "No QR";

    toggleEditFields();   // Important: Call after setting values
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// Initialize Add Form
toggleAddFields();
</script>

<?php require_once __DIR__.'/inc/footer.php'; ?>
