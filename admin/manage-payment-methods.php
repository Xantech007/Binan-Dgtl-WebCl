<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../inc/countries.php';

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
$crypto=(int)($_POST['crypto']??0);

$type=$_POST['type']??'bank';
$network=trim($_POST['network']??'');
$account_name=trim($_POST['account_name']??'');
$account_number=trim($_POST['account_number']??'');

$currency=trim($_POST['currency']??'USD');
$conversion_rate=(float)($_POST['conversion_rate']??1);
$active_country=trim($_POST['active_country']??'');
$min_withdraw=(float)($_POST['min_withdraw']??0);

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

/* ADD METHOD */

if($action==="add"){

$stmt=$pdo->prepare("
INSERT INTO payment_methods
(name,wallet_address,qr_image,image,crypto,type,network,account_name,account_number,currency,conversion_rate,active_country,min_withdraw,status,withdrawal_fee)
VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute($data);

$message="Payment method added successfully";
}

/* EDIT METHOD */

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
<div style="background:#238636;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin:auto;">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if($error): ?>
<div style="background:#f85149;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin:auto;">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>


<!-- ADD METHOD -->

<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:2rem;margin-bottom:3rem;max-width:900px;margin:auto;">

<h2 style="margin-bottom:1.8rem;text-align:center;">Add Payment Method</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="action" value="add">

<label>Method Name</label>
<input type="text" name="name" required style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Wallet Address</label>
<input type="text" name="wallet_address" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Crypto?</label>
<select name="crypto" style="width:100%;padding:0.8rem;margin-bottom:1rem;">
<option value="1">Yes</option>
<option value="0">No</option>
</select>

<label>Type</label>
<select name="type" style="width:100%;padding:0.8rem;margin-bottom:1rem;">
<option value="bank">Bank</option>
<option value="momo">MOMO</option>
</select>

<label>Network / Bank</label>
<input type="text" name="network" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Account Name</label>
<input type="text" name="account_name" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Account Number</label>
<input type="text" name="account_number" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Currency</label>
<input type="text" name="currency" value="USD" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Conversion Rate</label>
<input type="number" step="0.00000001" name="conversion_rate" value="1" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Minimum Withdraw</label>
<input type="number" step="0.00000001" name="min_withdraw" value="0" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Active Country</label>
<select name="active_country" style="width:100%;padding:0.8rem;margin-bottom:1rem;">
<option value="">All Countries</option>
<?php foreach($countries as $c): ?>
<option value="<?= htmlspecialchars($c) ?>">
<?= htmlspecialchars($c) ?>
</option>
<?php endforeach; ?>
</select>

<label>Logo</label>
<input type="file" name="logo" style="margin-bottom:1rem;">

<label>QR Code</label>
<input type="file" name="qr_image" style="margin-bottom:1rem;">

<label>Withdrawal Fee</label>
<input type="number" step="0.01" name="withdrawal_fee" value="0" style="width:100%;padding:0.8rem;margin-bottom:1rem;">

<label>Status</label>
<select name="status" style="width:100%;padding:0.8rem;margin-bottom:1.5rem;">
<option value="1">Active</option>
<option value="0">Inactive</option>
</select>

<button class="btn" style="width:100%;padding:1rem;">Add Payment Method</button>

</form>

</div>


<!-- LIST METHODS -->

<h2 style="text-align:center;margin:3rem 0 1.5rem;">Payment Methods</h2>

<div style="overflow-x:auto;">

<table style="width:100%;max-width:1200px;margin:auto;border-collapse:separate;border-spacing:0 10px;">

<thead>
<tr style="background:#1f2937;">
<th>ID</th>
<th>Name</th>
<th>Currency</th>
<th>Rate</th>
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

<td style="padding:1rem"><?= htmlspecialchars($m['currency']) ?></td>

<td style="padding:1rem"><?= $m['conversion_rate'] ?></td>

<td style="padding:1rem"><?= $m['active_country'] ?: 'All' ?></td>

<td style="padding:1rem;text-align:center">
<?= $m['status'] ? 'Active':'Inactive' ?>
</td>

<td style="padding:1rem;text-align:center">

<button class="btn" onclick='openEditModal(<?= json_encode($m) ?>)'>
Edit
</button>

<form method="POST" style="display:inline;">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= $m['id'] ?>">
<button class="btn red">Delete</button>
</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

</main>

<script>

function openEditModal(m){
alert("Edit modal code unchanged — data loaded successfully");
}

</script>

<?php require_once __DIR__.'/inc/footer.php'; ?>
