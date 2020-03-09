<? 
$link = $_GET['pages'];
if($link != "login"){ ?>

  <!-- Footer -->
  <footer class="sticky-footer bg-white" style="    position: absolute;
    width: 100%;
    overflow: hidden;
    bottom: 0;
    right: 0;
    z-index: 1;">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; Your Website 2019</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="<?php echo $site_url; ?>/logout">Logout</a>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
  <!-- Bootstrap core JavaScript-->
  <script src="<?php echo $site_url; ?>/vendor/jquery/jquery.min.js"></script>
  <script src="<?php echo $site_url; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="<?php echo $site_url; ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

  
  <!-- Page level plugins -->
  <script src="<?php echo $site_url ?>/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="<?php echo $site_url ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <link href="<?php echo $site_url ?>/css/summernote.css" rel="stylesheet">
  <script src="<?php echo $site_url ?>/js/demo/datatables-demo.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="<?php echo $site_url ?>/js/sb-admin-2.min.js"></script>
</body>

</html>
