<!doctype html>
<html>
<?php
$price_old = '1598';
$price_new = '799';
$discount = round((($price_old - $price_new) / $price_old) * 100);
$price_new2 = '789';
$price_new3 = '779';
$price_new4 = '769';

$product_id = '5';
$fbp = (isset($_GET['fbp'])) ? trim($_GET['fbp']) : '';
$_SESSION['fbp'] = $fbp;
?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="content-type" content="text/html" />
	<meta name="author" content="RAMStudio ( wpdew )" />

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css"
		integrity="sha512-SbiR/eusphKoMVVXysTKG/7VseWii+Y3FdHrt0EpKgpToZeemhqHeZeLWLhJutz/2ut2Vw1uQEj2MbRF+TVBUA=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet"
		href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.2/font/bootstrap-icons.min.css"
		integrity="sha512-YFENbnqHbCRmJt5d+9lHimyEMt8LKSNTMLSaHjvsclnZGICeY/0KYEeiHwD1Ux4Tcao0h60tdcMv+0GljvWyHg=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
		integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" 
		integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w==" 
		crossorigin="anonymous" referrerpolicy="no-referrer" />    

	<title>Quick landing page</title>
	<style>
		.btn-buy {
			display: block;
			margin: 0 auto;
		}
	</style>
</head>

<body>

	<div class="container text-center">
		<div class="row">

			<div class="col-md-6 offset-md-3 col-xs-12"  data-aos="fade-up">
				<h1>AdminOrder</h1>

				<div class="card mt-4 mb-4">
					<svg class="bd-placeholder-img card-img-top" width="100%" height="380px"
						xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Image cap"
						preserveAspectRatio="xMidYMid slice" focusable="false">
						<title>Placeholder</title>
						<rect width="100%" height="100%" fill="#868e96"></rect><text x="43%" y="50%" fill="#dee2e6"
							dy=".3em">Image cap</text>
					</svg>

					<div class="card-body">
						<h5 class="card-title">Card title</h5>
						<p class="card-text">
							Опис товару. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris.
						</p>

						<button type="button" class="btn btn-success js-get-product" data-bs-toggle="modal"
							data-bs-target="#orderModal" data-id="5" data-price="750"
							data-name="Тактичні перчатки Mechanix">
							<i class="bi bi-cart"></i> Зробити замовлення
						</button>

					</div>
				</div>

			</div>


			<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModal" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="order.php" method="post">
								<h3 class="mb-3">Заповніть, будь ласка, форму нижче</h3>
								<input class="form-control form-control-lg" type="text" placeholder="Ваше ім'я"
									name="name" required="">
								<input class="form-control form-control-lg mt-3" type="text"
									placeholder="Ваш номер телефону" name="phone" required="" />

								<input type="hidden" name="product" id="hiddenProduct"  value="Quick landing page" hidden="hidden" />
								<input type="hidden" name="product_id" id="product_id" value="<?= $product_id; ?>" hidden="hidden" />
								<input type="hidden" name="product_price" id="product_price"  value="<?= $price_new; ?>" hidden="hidden" />
								<input type="hidden" name="fbp" id="fbp"  value="<?= $fbp; ?>" hidden="fbp" />
								<input type="hidden" name="count" value="1"/>
								<input type="hidden" name="servername" value="<?= dirname($_SERVER['SCRIPT_NAME']);?>">
								<input type="hidden" name="type" value="offer">
								<button type="submit" class="btn btn-danger btn-buy mt-4 mb-2">
									Зробити замовлення
								</button>

								<span class="mt-4 text-center" style="font-size: 12px">
									Відправляючи дані ви погоджуєтесь з <br />
									<a href="politics.html" target="_blank">
									політикою конфіденційності</a>
								</span>
							</form>
						</div>
					</div>
				</div>
			</div>

		</div>
		<a href="polit.php">Полiтика конфiденцiйностi</a><br>
		<a href="ofert.php">Оферта</a>
	</div>

	


	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
		integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js"
		integrity="sha512-1/RvZTcCDEUjY/CypiMz+iqqtaoQfAITmNSJY17Myp4Ms5mdxPS5UV7iOfdZoxcGhzFbOm6sntTKJppjvuhg4g=="
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js" 
		integrity="sha512-d4KkQohk+HswGs6A1d6Gak6Bb9rMWtxjOa0IiY49Q3TeFd5xAzjWXDCBW9RS7m86FQ4RzM2BdHmdJnnKRYknxw==" 
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>   
	<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" 
		integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" 
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>     

	<script>
	
		AOS.init();
		$('input[name=phone]').mask('+38(999)-999-99-99');
	
		$(document).on('click', '.js-get-product', function () {
			let product_id = $(this).attr('data-id');
			let product_price = $(this).attr('data-price');
			let product_name = $(this).attr('data-name');

			$('input[name=product_id]').val(product_id);
			$('input[name=product_price]').val(product_price);
			$('input[name=product_name]').val(product_name);
		})
	</script>
</body>

</html>