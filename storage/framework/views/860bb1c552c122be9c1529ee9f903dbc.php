 <?php if(Session::has('id')){

}else{
	
	header("Location: https://stack.mobileappdemo.net/");
            die; 

} 
      
?>
<div id="content">
    <!-- topbar -->
    <div class="topbar">
       <nav class="navbar navbar-expand-lg navbar-light">
          <div class="full">
             <button type="button" id="sidebarCollapse" class="sidebar_toggle"><i class="fa fa-bars"></i></button>
             <div class="logo_section">
                
                <a href="index.html"><img class="img-responsive" src="https://stack.mobileappdemo.net/assets/pasawin-logo.png" alt="#" style="height: 55px;" /></a>
             </div>
             <div class="right_topbar">
                <div class="icon_info">
                   
                   <ul class="user_profile_dd">
                      <li>
                         <a class="dropdown-toggle" data-toggle="dropdown"><img class="img-responsive rounded-circle"
                         src="https://root.bdgcassino.com/public/images/layout_img/user_img.jpg" alt="#" /><span class="name_user">Admin</span></a>
                         <div class="dropdown-menu">
                            <!--<a class="dropdown-item" href="#">My Profile</a>-->
                            
                            <a class="dropdown-item" href="<?php echo e(route('auth.logout')); ?>"><span>Log Out</span> <i class="fa fa-sign-out"></i></a>
                         </div>
                      </li>
                   </ul>
                </div>
             </div>
          </div>
       </nav>
    </div>
    <!-- end topbar -->
<!-- jQuery + Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


 <?php /**PATH /var/www/vhosts/pasawin.com/root.pasawin.com/resources/views/admin/body/header.blade.php ENDPATH**/ ?>