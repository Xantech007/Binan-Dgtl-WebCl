<?php
require_once __DIR__ . '/inc/header.php';

$message='';
$error='';

$qr_upload_dir=__DIR__.'/../assets/images/qr/';
$logo_upload_dir=__DIR__.'/../assets/images/';

$qr_prefix='assets/images/qr/';
$logo_prefix='assets/images/';

if(!is_dir($qr_upload_dir)) mkdir($qr_upload_dir,0755,true);
if(!is_dir($logo_upload_dir)) mkdir($logo_upload_dir,0755,true);

if($_SERVER['REQUEST_METHOD']==='POST'){

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

/* QR UPLOAD */

if(!empty($_FILES['qr_image']['name'])){

$file=$_FILES['qr_image'];
$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
$allowed=['jpg','jpeg','png','webp'];

if(!in_array($ext,$allowed)){
throw new Exception("Invalid QR format");
}

$new_name="qr_".time().".".$ext;
$target=$qr_upload_dir.$new_name;

if(!move_uploaded_file($file['tmp_name'],$target)){
throw new Exception("QR upload failed");
}

$qr_image_path=$qr_prefix.$new_name;
}

/* LOGO UPLOAD */

if(!empty($_FILES['logo']['name'])){

$file=$_FILES['logo'];
$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
$allowed=['jpg','jpeg','png','webp'];

if(!in_array($ext,$allowed)){
throw new Exception("Invalid logo format");
}

$safe=strtolower(preg_replace('/[^a-z0-9]+/','-', $name));
$new_logo=$safe.".".$ext;

$target=$logo_upload_dir.$new_logo;

if(!move_uploaded_file($file['tmp_name'],$target)){
throw new Exception("Logo upload failed");
}

$logo_path=$logo_prefix.$new_logo;
}

$data=[
$name,
$wallet_address,
$qr_image_path,
$logo_path,
$crypto,
$type,
$network,
$account_name,
$account_number,
$currency,
$conversion_rate,
$active_country,
$min_withdraw,
$status,
$withdrawal_fee
];

if($action==="add"){

$stmt=$pdo->prepare("
INSERT INTO payment_methods
(name,wallet_address,qr_image,image,crypto,type,network,account_name,account_number,currency,conversion_rate,active_country,min_withdraw,status,withdrawal_fee)
VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute($data);

$message="Payment method added successfully";

}

if($action==="edit"){

$id=(int)$_POST['id'];

$data[]=$id;

$stmt=$pdo->prepare("
UPDATE payment_methods SET
name=?,
wallet_address=?,
qr_image=?,
image=?,
crypto=?,
type=?,
network=?,
account_name=?,
account_number=?,
currency=?,
conversion_rate=?,
active_country=?,
min_withdraw=?,
status=?,
withdrawal_fee=?
WHERE id=?
");

$stmt->execute($data);

$message="Payment method updated";

}

}catch(Exception $e){
$error=$e->getMessage();
}

}

/* DELETE METHOD */

if(isset($_POST['action']) && $_POST['action']=="delete"){

$id=(int)$_POST['id'];

$stmt=$pdo->prepare("SELECT qr_image,image FROM payment_methods WHERE id=?");
$stmt->execute([$id]);
$files=$stmt->fetch(PDO::FETCH_ASSOC);

if($files['qr_image'] && file_exists(__DIR__.'/../'.$files['qr_image'])){
unlink(__DIR__.'/../'.$files['qr_image']);
}

if($files['image'] && file_exists(__DIR__.'/../'.$files['image'])){
unlink(__DIR__.'/../'.$files['image']);
}

$stmt=$pdo->prepare("DELETE FROM payment_methods WHERE id=?");
$stmt->execute([$id]);

$message="Payment method deleted";

}

/* LOAD METHODS */

$stmt=$pdo->query("SELECT * FROM payment_methods ORDER BY id DESC");
$methods=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>

<h1 style="text-align:center;margin:2.5rem 0 2rem;">Manage Payment Methods</h1>

<?php if($message): ?>
<div style="background:#238636;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin-left:auto;margin-right:auto;">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if($error): ?>
<div style="background:#f85149;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin-left:auto;margin-right:auto;">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- ADD METHOD -->

<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:2rem;margin-bottom:3rem;max-width:900px;margin-left:auto;margin-right:auto;">

<h2 style="margin-bottom:1.8rem;text-align:center;">Add Payment Method</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="action" value="add">

<div style="margin-bottom:1.4rem;">
<label>Method Name</label>
<input type="text" name="name" required style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Wallet Address</label>
<input type="text" name="wallet_address" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Crypto?</label>
<select name="crypto" style="width:100%;padding:0.8rem;">
<option value="1">Yes</option>
<option value="0">No</option>
</select>
</div>

<div style="margin-bottom:1.4rem;">
<label>Type</label>
<select name="type" style="width:100%;padding:0.8rem;">
<option value="">None</option>
<option value="bank">Bank</option>
<option value="momo">MOMO</option>
</select>
</div>

<div style="margin-bottom:1.4rem;">
<label>Network / Bank</label>
<input type="text" name="network" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Account Name / MOMO Name</label>
<input type="text" name="account_name" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Account Number / MOMO Number</label>
<input type="text" name="account_number" style="width:100%;padding:0.8rem;">
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
<input type="number" step="0.01" name="withdrawal_fee" value="0">
</div>

<div style="margin-bottom:1.4rem;">
<label>Currency</label>
<input type="text" name="currency" value="USD" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Conversion Rate</label>
<input type="number" step="0.00000001" name="conversion_rate" value="1" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Active Country (optional)</label>
<input type="text" name="active_country" placeholder="Nigeria,Ghana or leave empty" style="width:100%;padding:0.8rem;">
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

<button type="submit" class="btn" style="width:100%;padding:1rem;">
Add Payment Method
</button>

</form>

</div>

<!-- LIST METHODS -->

<h2 style="text-align:center;margin:3rem 0 1.5rem;">Payment Methods</h2>

<div style="overflow-x:auto;">

<table style="width:100%;max-width:1100px;margin:0 auto;border-collapse:separate;border-spacing:0 10px;">

<thead>
<tr style="background:#1f2937;">
<th>ID</th>
<th>Name</th>
<th>Logo</th>
<th>QR</th>
<th>Currency</th>
<th>Rate</th>
<th>Min Withdraw</th>
<th>Country</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($methods as $m): ?>

<tr style="background:var(--card);">

<td style="padding:1rem;text-align:center"><?= $m['id'] ?></td>

<td style="padding:1rem"><?= htmlspecialchars($m['name']) ?></td>

<td style="padding:1rem;text-align:center">
<?php if($m['image']): ?>
<img src="../<?= $m['image'] ?>" style="max-width:60px;">
<?php endif; ?>
</td>

<td style="padding:1rem;text-align:center">
<?php if($m['qr_image']): ?>
<img src="../<?= $m['qr_image'] ?>" style="max-width:60px;">
<?php endif; ?>
</td>

<td style="padding:1rem;text-align:center">
<?= $m['status'] ? 'Active':'Inactive' ?>
</td>

<td style="padding:1rem;text-align:center">

<button class="btn" style="margin-right:6px"
onclick='openEditModal(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
Edit
</button>

<form method="POST" style="display:inline;">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= $m['id'] ?>">
<button class="btn red">Delete</button>
</form>

</td>

<td><?= htmlspecialchars($m['currency']) ?></td>

<td><?= number_format($m['conversion_rate'],8) ?></td>

<td><?= number_format($m['min_withdraw'],2) ?></td>

<td><?= htmlspecialchars($m['active_country'] ?: 'All') ?></td>
  
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

<!-- EDIT MODAL -->

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
<input type="text" name="name" id="edit_name" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Wallet Address</label>
<input type="text" name="wallet_address" id="edit_wallet" style="width:100%;padding:0.8rem;">
</div>

<div style="margin-bottom:1.4rem;">
<label>Crypto?</label>
<select name="crypto" id="edit_crypto" style="width:100%;padding:0.8rem;">
<option value="1">Yes</option>
<option value="0">No</option>
</select>
</div>

<div style="margin-bottom:1.4rem;">
<label>Type</label>
<select name="type" id="edit_type" style="width:100%;padding:0.8rem;">
<option value="">None</option>
<option value="bank">Bank</option>
<option value="momo">MOMO</option>
</select>
</div>

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

<div style="margin-bottom:1.4rem;">
<label>Current Logo</label>
<div id="edit_logo_preview"></div>
<input type="file" name="logo">
</div>

<div style="margin-bottom:1.4rem;">
<label>Current QR</label>
<div id="edit_qr_preview"></div>
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
</div>

<div style="margin-bottom:1.4rem;">
<label>Active Country</label>
<input type="text" name="active_country" id="edit_country" style="width:100%;padding:0.8rem;">
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

<button class="btn" style="width:100%;padding:1rem;">
Save Changes
</button>

</form>

</div>

</div>
  
</main>

<script>

function openEditModal(m){

document.getElementById("editModal").style.display="flex";

document.getElementById("edit_id").value=m.id;
document.getElementById("edit_name").value=m.name;
document.getElementById("edit_wallet").value=m.wallet_address || "";
document.getElementById("edit_crypto").value=m.crypto;
document.getElementById("edit_type").value=m.type || "";
document.getElementById("edit_network").value=m.network || "";
document.getElementById("edit_account_name").value=m.account_name || "";
document.getElementById("edit_account_number").value=m.account_number || "";
document.getElementById("edit_fee").value=m.withdrawal_fee || "0";
document.getElementById("edit_currency").value=m.currency || "USD";
document.getElementById("edit_rate").value=m.conversion_rate || 1;
document.getElementById("edit_country").value=m.active_country || "";
document.getElementById("edit_min_withdraw").value=m.min_withdraw || 0;
document.getElementById("edit_status").value=m.status;

document.getElementById("edit_current_qr").value=m.qr_image || "";
document.getElementById("edit_current_logo").value=m.image || "";

document.getElementById("edit_logo_preview").innerHTML=
m.image ? `<img src="../${m.image}" style="max-width:80px">` : "No logo";

document.getElementById("edit_qr_preview").innerHTML=
m.qr_image ? `<img src="../${m.qr_image}" style="max-width:80px">` : "No QR";

}

function closeEditModal(){
document.getElementById("editModal").style.display="none";
}

</script>

<?php require_once __DIR__.'/inc/footer.php'; ?>
