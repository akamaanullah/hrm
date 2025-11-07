<?php include "header.php"?>

<?php include "top-bar.php"?>

<?php include "sidebar.php"?>


<div class="main-content">
    <div class="announcement-wrapper">
        <div class="announcement-header d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Add Announcement</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="fas fa-plus me-1"></i> Add Announcement
            </button>
        </div>
        <div class="filter-form">
            <div class="row g-2 align-items-end">
                <div class="col-lg-9 col-md-9 col-sm-12">
                    <label for="" class="form-label">Search Announcements</label>
                    <input type="text" class="form-control" id="" placeholder="Search Announcements...">
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100" id="searchBtn"
                        style="height:38px;">Search</button>
                </div>
            </div>
            <div id="noResults" class="text-center text-dark mt-3" style="display:none;"> No announcements found.</div>
        </div>

        <div class="announcement-timeline" id="announcementTimeline">
            <div class="announcement-item">
                <div class="announcement-actions float-end">
                    <a href="#" class="text-primary edit-btn"  title="Edit"
                        data-bs-toggle="modal"><i class="fas fa-edit"></i></a>
                    <a href="#" class="text-danger delete-btn" 
                        title="Delete"><i class="fas fa-trash-alt"></i></a>
                </div>
                <div class="announcement-icon theme-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="announcement-content-wrapper">
                    <div class="announcement-time">
                        <span class="announcement-date theme-badge"></span>
                        <i class="far fa-clock"></i> 
                    </div>
                    <h3 class="announcement-title"></h3>
                    <div class="announcement-content">
                       
                    </div>
                    <div class="announcement-footer">
                        <span class="announcement-category">
                            <i class="fas fa-tag"></i> 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnnouncementModalLabel">Add Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="announcementTitle" placeholder="Enter title">
                    </div>
                    <div class="mb-3">
                        <label for="announcementEditor" class="form-label">Content</label>
                        <div id="announcementEditor" style="height: 200px;" class="form-control p-0"></div>
                    </div>
                    <div class="mb-3">
                        <label for="announcementStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="announcementStartDate">
                    </div>
                    <div class="mb-3">
                        <label for="announcementEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="announcementEndDate">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAnnouncement">Add</button>
            </div>
        </div>
    </div>
</div>


<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="announcementTitle" placeholder="Enter title">
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementEditor" class="form-label">Content</label>
                        <div id="editAnnouncementEditor" style="height: 200px;" class="form-control p-0"></div>
                    </div>
                    <div class="mb-3">
                        <label for="announcementStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="announcementStartDate">
                    </div>
                    <div class="mb-3">
                        <label for="announcementEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="announcementEndDate">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="admin-annoucement.php"><button type="button" class="btn btn-primary" id="updateAnnouncement">Update</button></a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
  <div id="announcementToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="announcementToastMsg">
        Announcement added successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>


<?php include "footer.php"?>
<!-- Quill rich text editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
  // Initialize Quill editors after page load
  document.addEventListener('DOMContentLoaded', function () {
    window.addAnnouncementQuill = new Quill('#announcementEditor', {
      theme: 'snow',
      placeholder: 'Write announcement... (bold, lists, links etc.)',
      modules: {
        toolbar: [
          [{ header: [1, 2, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ 'color': [] }, { 'background': [] }],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          ['link', 'blockquote', 'code-block'],
          ['clean']
        ]
      }
    });

    window.editAnnouncementQuill = new Quill('#editAnnouncementEditor', {
      theme: 'snow',
      placeholder: 'Edit announcement...',
      modules: {
        toolbar: [
          [{ header: [1, 2, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ 'color': [] }, { 'background': [] }],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          ['link', 'blockquote', 'code-block'],
          ['clean']
        ]
      }
    });
  });
</script>

<script src="include/js/announcement.js"></script>