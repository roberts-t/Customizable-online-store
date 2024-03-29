<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_token'])) {
    // Generate CSRF token
    $_SESSION['user_token'] = bin2hex(random_bytes(16));
}
include_once 'conn.php';
$cartItemCount = 0;
$cartItems = "";
// Set tab as empty string if there is no active header tab
if (!isset($tab)) {
    $tab = '';
}

// Check if user CSRF token matches
function isFormTokenValid($token = '') {
    $userToken = $token ?? '';
    if (isset($_SESSION['user_token']) && hash_equals($_SESSION['user_token'], $userToken)) {
        return true;
    }
    return false;
}

// Include script which will set up search bar and header cart information
?>
<script>
var objShop;
    $( function()
    {
        objShop = new ShopScript();
        objShop.initSearch(<?=json_encode($tab ?? 'None')?>)
        objShop.updateCartPreview();
    } );
</script>

<?php
// Check if there a sign in/up result data to show and open auth modal with correct tab
if (isset($_SESSION['sign_error']) || (isset($_SESSION['sign_success']) && isset($_GET['su']) && $_GET['su'] == '1'))
{
?>
    <script>
    $( document ).ready(function() {
        $('#authModal').modal('show');
    });
    </script>
<?php
    if (isset($_SESSION['sign_error']['email']) || isset($_SESSION['sign_error']['password']))
    {
    ?>
    <script>
    $( document ).ready(function() {
        objShop.showSignUpForm();
    });
    </script>
    <?php
    }
    // Save error in variable and delete session variable
    $formError = $_SESSION['sign_error'] ?? array();
    unset($_SESSION['sign_error']);
}

?>

<header>
    <div class="navbar navbar-expand-lg navbar-light nav-bot" role="navigation">
        <nav class="container navbar-expand-lg navbar-dark">
                <div class="row">
                    <div class="col-md-6 col-7 col-lg-3 d-flex logo-col">
                        <button class="mobile-sidebar-toggle me-3 d-block d-sm-none" type="button" onclick="objShop.showSideBar()">
                            <i class="fas fa-bars"></i>
                        </button>

                        <a class="navbar-brand text-white m-auto" href="index.php">
                            <img src="images/<?=htmlspecialchars($storeSettings['logo_path'])?>" class="<?=$storeSettings['logo_orientation'] ?? 'horizontal'?>" alt="logo">
                        </a>
                    </div>

                    <div class="col-lg-6 col-md-3 col-2 d-flex search-col">
                        <div class="search-container m-auto ps-2 pe-4 d-none d-lg-block">
                                <div class="input-group">
                                    <input class="form-control" id="search" type="search" name="q" value="<?= isset($_GET['q']) ? htmlspecialchars(urldecode($_GET['q']), ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Product" aria-label="Search">
                                    <button id="search_button" class="btn search-button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                        </div>

                        <div onclick="objShop.mobSearch()" class="search-button-mobile ms-auto align-items-center d-flex d-lg-none">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <div class="col-md-3 col-3 ps-0 pe-4 d-flex justify-content-end cart-col">
                        <div class="cart-container" onclick="location.href='cart.php'">
                            <div class="relative-container position-relative d-flex align-items-center text-center">
                                <div class="cart-icon-container">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="cart-info-container">
                                    <div class="cart-count-container fw-bold">0
                                    </div>
                                    <div class="cart-text-container fw-bold">
                                        0.00
                                        <span>€</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </nav>
    </div>
    
    <div class="navbar navbar-expand navbar-light nav-top d-none d-sm-flex">
        <nav class="container navbar-expand navbar-light d-none d-sm-flex" role="navigation">
                
                <ul class="navbar-nav w-100">
                    <li class="nav-item border-end text-center">
                        <a class="nav-link py-0 <?=$tab === 'Products' ? 'active' : ''?>" aria-current="page" href="index.php">
                            <i class="fas fa-boxes"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item border-end text-center">
                        <a class="nav-link py-0 <?=$tab === 'About' ? 'active' : ''?>" href="about.php">
                            <i class="fas fa-users"></i>
                            <span>About us</span>
                        </a>
                    </li>
                    <li class="nav-item text-center">   
                        <a class="nav-link py-0 <?=$tab === 'Contact' ? 'active' : ''?>" href="contact.php">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    
                    <li class="ms-auto text-center nav-item <?php echo (isset($_SESSION['user_id'])) ? 'd-none' : '' ?>">   
                        <a class="nav-link py-0" onclick="objShop.hideAuthModalErrors()" data-bs-toggle="modal" data-bs-target="#authModal">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Sign in</span>
                        </a>
                    </li>
                    
                    <li class="ms-auto text-center nav-item <?php echo (isset($_SESSION['user_id'])) ? '' : 'd-none' ?>">
                        <div class="dropdown">
                            <a class="nav-link py-0" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span class="dropdown-toggle">Account</span>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="account.php">Account settings</a></li>
                                    <li><a class="dropdown-item" href="history.php">Order history</a></li>
                                    <?=(($_SESSION['user_role'] == 1) ? '<li><a class="dropdown-item" href="admin_dash.php">Admin dashboard</a></li>' : '')?>
                                    <li><a class="dropdown-item" href="logout.php?token=<?=$_SESSION['user_token'] ?? ''?>">Log out</a></li>
                                </ul>
                            </a>
                        </div>
                    </li>
                    
                </ul>
        </nav>
    </div>
    
    <div class="mobile-sidebar-container">
        <div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white mobile-sidebar-content" style="width: 280px;">
            <a href="/" class="d-flex align-items-center mb-1 mb-md-0 me-md-auto text-white text-decoration-none">
                <img class="me-2 img-fluid" src="images/<?=htmlspecialchars($storeSettings['logo_path'])?>" alt="logo" height="50">
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item py-1">
                    <a href="index.php" class="nav-link text-white <?=$tab === 'Products' ? 'active' : ''?>" aria-current="page">
                        <i class="fas fa-boxes"></i>
                        Products
                    </a>
                </li>
                <li class="nav-item py-1">
                    <a href="about.php" class="nav-link text-white <?=$tab === 'About' ? 'active' : ''?>">
                        <i class="fas fa-users"></i>
                        About us
                    </a>
                </li>
                <li class="nav-item py-1">
                    <a href="contact.php" class="nav-link text-white <?=$tab === 'Contact' ? 'active' : ''?>">
                        <i class="far fa-envelope"></i>
                        Contact
                    </a>
                </li>
                
                <li class="nav-item py-1 <?php echo (isset($_SESSION['user_id'])) ? 'd-none' : '' ?>">
                    <a class="nav-link py-0 text-white" onclick="objShop.hideSideBar()" data-bs-toggle="modal" data-bs-target="#authModal">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign in</span>
                    </a>
                </li>
                
                <li class="nav-item py-1 <?php echo (isset($_SESSION['user_id'])) ? '' : 'd-none' ?>">
                    <div class="dropdown">
                        <a class="nav-link py-0 text-white" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <span class="dropdown-toggle">Account</span>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item" href="account.php">Account settings</a></li>
                                <li><a class="dropdown-item" href="history.php">Order history</a></li>
                                <?=(($_SESSION['user_role'] == 1) ? '<li><a class="dropdown-item" href="admin_dash.php">Admin dashboard</a></li>' : '')?>
                                <li><a class="dropdown-item" href="logout.php?token=<?=$_SESSION['user_token'] ?? ''?>">Log out</a></li>
                            </ul>
                        </a>
                    </div>
                </li>
                
            </ul>
            <div class="mobile-sidebar-close" onclick="objShop.hideSideBar()">
              <i class="fas fa-times-circle"></i>
            </div>
          </div>
      </div>
</header>

<body>

<div class="modal" id="authModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="px-2 py-1 fw-bold fs-3 m-auto modal-title">
                        Sign in
                    </div>
                    <button type="button" class="close-modal" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times-circle"></i></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="login.php" id="login_form" novalidate>
                    
                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
                    
                        <label for="formEmail" class="form-label">Email address *</label>
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text text-secondary"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control <?php echo (isset($formError['email'])) ? 'is-invalid' : '' ?>" id="formEmail" name="email" value="<?=htmlspecialchars($_SESSION['auth_email'] ?? '')?>">
                            <div class="invalid-feedback">
                                <?php echo (isset($formError['email'])) ? htmlspecialchars($formError['email']) : ''?>
                            </div>
                        </div>
                        
                        <label for="formPass" class="form-label">Password *</label>
                        <div class="input-group input-group-lg mb-2 ">
                            <span class="input-group-text text-secondary"><i class="fas fa-unlock-alt"></i></span>
                            <input type="password" class="form-control <?php echo (isset($formError['password'])) ? 'is-invalid' : '' ?>" id="formPass" name="password">
                            <div class="invalid-feedback">
                                <?php echo (isset($formError['password'])) ? htmlspecialchars($formError['password']) : ''?>
                            </div>
                        </div>
                        
                        <label for="formPassRepeat" class="form-label d-none form-pass-repeat mt-2">Confirm Password *</label>
                        <div class="input-group input-group-lg mb-4 d-none form-pass-repeat">
                            <span class="input-group-text text-secondary"><i class="fas fa-unlock-alt"></i></span>
                            <input type="password" class="form-control" id="formPassRepeat" name="c_password">
                        </div>
                        
                        
                        <div style="min-height: 21px;" class="login-gn-error invalid-feedback d-block text-center mb-3 <?=isset($formError['general']) ? 'visible' : 'invisible'?>"><?= $formError['general'] ?? '' ?></div>
                        
                        <div style="min-height: 21px;" class="register-success text-success text-center mb-3 <?=isset($_SESSION['sign_success']) ? '' : 'd-none'?>"><?= $_SESSION['sign_success'] ?? '' ?></div>
                        
                        <?php if(isset($_SESSION['sign_success'])) unset($_SESSION['sign_success'])?>
                        
                        <div class="col-md-12 mb-3 text-center form-submit">
                            <button type="submit" class="w-100 py-2 text-uppercase fw-bold rounded-0 btn btn-block btn-primary">Login</button>
                        </div>
                        
                        <div class="col-md-12 mb-2 text-center form-signup-option">
                            <span>Not a member? <a onclick="objShop.showSignUpForm(); objShop.hideAuthModalErrors();" class="link-primary">Sign up now</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
