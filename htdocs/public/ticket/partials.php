<?php 

function printTheHeader($title, $cssArray, $headerjs = array()){
	echo '
		<!DOCTYPE html>		
		<html class="no-js js_active  vc_desktop  vc_transform  vc_transform " lang="es">
		<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">    
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>$title</title>
	';

	if(!empty($cssArray)){
		foreach ($cssArray as $key => $value) {
			echo '<link rel="stylesheet" href="'.$value.'" type="text/css">'."\n";
		}
	}

	if(!empty($headerjs)){
		foreach ($headerjs as $key => $value) {
			echo '<script type="text/javascript" src="'.$value.'"></script>'."\n";
		}
	}

	echo '
		<body class="page-template-default page">
	';
}

function printTheContainer(){
	echo '
	<div class="page-wrapper js-full-height" style="min-height: 723px;">
	<div class="main-wrapper">
			
	<header class="js-header cs-header cs-header--simple cs-header-t cs-header-bg" style="background-color:#ffffff;">
		<div class="container">
		<div class="cs-header__wrap">
			<a href="https://multisistemas.com.sv/" class="logo">
			<img src="img/logo-1.png" alt="Multisistemas" class="logo">
			<img src="img/logo-light.png" alt="Multisistemas" class="logo-light">
			<img src="img/logo-1.png" alt="Multisistemas" class="logo-scroll">
			</a>
		<div class="cs-header__overlay js-menu-toggle"></div>
		<div class="cs-header__inner">
		<div class="cs-header__menu">
			<ul id="menu-main-menu" class="menu"><li id="menu-item-4920" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home menu-item-4920"><a href="https://multisistemas.com.sv/">Inicio</a></li>
				<li id="menu-item-4943" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4943"><a href="https://multisistemas.com.sv/about/">Nosotros</a></li>
				<li id="menu-item-5193" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5193"><a href="https://multisistemas.com.sv/politica-de-privacidad/#">Servicios</a>
					<ul class="sub-menu">
						<li id="menu-item-4940" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4940"><a href="https://multisistemas.com.sv/e-learning/">Plataformas eLearning</a></li>
						<li id="menu-item-4941" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4941"><a href="https://multisistemas.com.sv/e-commerce/">Soluciones eCommerce</a></li>
						<li id="menu-item-4951" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4951"><a href="https://multisistemas.com.sv/mobile-apps/">Aplicaciones Móviles</a></li>
					</ul>
				</li>
				<li id="menu-item-5090" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5090"><a href="https://multisistemas.com.sv/blog/">Blog</a></li>
				<li id="menu-item-5331" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5331"><a href="https://multisistemas.com.sv/contact-us/">Contacto</a></li>
			</ul>
		</div>							
		</div>
		<div class="cs-header__mobile-btn js-menu-toggle">
			<span></span>
			<span></span>
			<span></span>
		</div>
		</div>
		</div>
	</header>

	<div class="hero-wrapper">
		<div class="container">
			<div class="hero">
	';

	
}

function printTheFooter($footerjs = array()){
	echo '
	</div>
	</div>
	</div>

	</div>
	</div>


	<footer class="cs-footer cs-footer--modern js-footer-animation -light cs-footer--mask s-back-switch" id="footer" style="background-image: url(&quot;img/footer-bg.jpg&quot;);">
		<img src="img/footer-bg.jpg" class="s-img-switch is-complete" alt="Footer background" style="display: none;">
		
		<svg width="1922px" class="cs-footer__mask" height="217px" viewBox="0 0 1922 217" xmlns="http://www.w3.org/2000/svg" style="transform: scaleY(1);">
				<g>
					<path d="M0.988371625,135.269267 C146.22925,167.486165 355.58855,179.318076 587.575,83.024 C1024.012,-98.134 1372.188,150.374 1539.271,195.521 C1696.47263,237.997731 1809.03598,207.529942 1921.08374,177.511669 L1921,1 C1921.26406,0.622444163 879.055352,0.801190242 330.240706,0.921484488 C134.10673,0.964474938 0.988371625,1 0.988371625,1 L0.988371625,135.269267 Z" opacity="0.15" class="js-path-change" data-opacity-to="1" data-opacity-from="0.15" style="transform: translateX(0%); opacity: 0.15;"></path>
					<path d="M1921.01227,87.9530535 C1893.93175,79.8806574 1866.4536,70.4950679 1838.649,59.655 C1751.26592,25.5871921 1667.4368,7.64851492 1587.90016,1.08519429 L1443.78733,0.963979318 C1194.76856,20.5603952 999.775534,135.603994 887.095,164.018 C720.064,206.138 603.368,171.098 484.128,141.418 C446.136203,131.962264 389.65936,74.7090019 330.256679,0.972652954 C1390.80805,0.972652954 1921.08374,0.938196728 1921.08374,0.869284275 L1921.01227,87.9530535 Z" opacity="0.1" class="js-path-change" data-opacity-to="1" data-move="13" data-opacity-from="0.1" style="transform: translateX(0%); opacity: 0.1;"></path>
					<path id="path1" class="js-over-logo" d="M871.374,83.008 C1268.234,-106.767 1548.948,85.736 1769.245,129.832 C1818.4714,139.685414 1869.34711,145.935967 1921,149.66748 L1921,1 C643.012499,1 3.01249938,1 1,1 L1,54.4902344 C36.7988629,77.2442515 60.6125506,91.755213 77.2,99.89 C165.26,143.074 474.515,272.783 871.374,83.008 Z"></path>
				</g>
		</svg>
		
		<div class="container">
			<div class="cs-footer__top">
				<div class="cs-footer__info">
					<div class="cs-footer__logo cs-footer__logo--img">
						<a href="https://multisistemas.com.sv/">
						<img src="img/logo-light.png" class="cs-footer__img cs-footer__img--light cs-footer__logo--light" alt="Multisistemas">
						<img src="img/logo-1.png" class="cs-footer__img cs-footer__img--dark cs-footer__logo--dark" alt="Multisistemas" style="clip-path: polygon(0% 0%, 100% 0px, 100% 100%, 0% 100%);">
						</a>
					</div>
					<div class="cs-footer__text">
							Soluciones de negocio y tecnologías de información
					</div>
				</div>
				<div class="cs-footer__widget">
					<div id="nav_menu-5" class="sidebar-item col-xs-12 col-sm-6 col-md-4 widget_nav_menu">
						<div class="item-wrap"><h5>Empresa</h5>
							<div class="menu-empresa-container">
							<ul id="menu-empresa" class="menu">
								<li id="menu-item-1931" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1931"><a href="https://multisistemas.com.sv/">Inicio</a>
								</li>
								<li id="menu-item-1932" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1932"><a href="https://multisistemas.com.sv/about-us">Nosotros</a></li>
								<li id="menu-item-1934" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1934"><a href="https://multisistemas.com.sv/blog">Blog</a></li>
								<li id="menu-item-1935" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1935"><a href="https://multisistemas.com.sv/contact-us">Contacto</a></li>
							</ul>
							</div>
						</div>
					</div>
					<div id="nav_menu-6" class="sidebar-item col-xs-12 col-sm-6 col-md-4 widget_nav_menu">
						<div class="item-wrap"><h5>Servicios</h5>
							<div class="menu-servicios-container">
								<ul id="menu-servicios" class="menu">
									<li id="menu-item-5209" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5209"><a href="https://multisistemas.com.sv/mobile-apps/">Aplicaciones móviles</a></li>
									<li id="menu-item-5210" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5210"><a href="https://multisistemas.com.sv/e-learning/">Plataformas eLearning</a></li>
									<li id="menu-item-5211" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5211"><a href="https://multisistemas.com.sv/e-commerce/">Soluciones eCommerce</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div id="nav_menu-7" class="sidebar-item col-xs-12 col-sm-6 col-md-4 widget_nav_menu">
						<div class="item-wrap"><h5>Enlaces</h5>
							<div class="menu-informacion-container">
								<ul id="menu-informacion" class="menu">
									<li id="menu-item-5222" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-privacy-policy current-menu-item page_item page-item-5215 current_page_item menu-item-5222"><a href="https://multisistemas.com.sv/politica-de-privacidad/" aria-current="page">Política de privacidad</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="cs-footer__bottom">			
				<div class="cs-footer__copy" style="width:100%;text-align:center;">
				Copyright © 2011 Multisistemas e Inversiones S. A. de C. V.
				</div>
			</div>
		</div>
	</footer>
	';

	if(!empty($footerjs)){
		foreach ($footerjs as $key => $value) {
			echo '<script type="text/javascript" src="'.$value.'"></script>'."\n";
		}
	}

	echo '
	</body>
	</html>
	';
}

?>