<?php include 'templates/header.php'; ?>
<link rel="stylesheet" href="assets/style.css">

<div class="container my-5">
  <h2 class="section-title text-center mb-4">Frequently Asked Questions</h2>

  <div class="row mb-5">
    <div class="col-md-6">
      <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
              How do I book an appointment?
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              Login as a client, go to the provider list, select a time slot, and click "Book".
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
              Can I cancel or reschedule my appointment?
            </button>
          </h2>
          <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              Yes, you can manage appointments from your client dashboard under "My Appointments".
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
              How are providers verified?
            </button>
          </h2>
          <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              All providers are verified by admin through registration and credential review.
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 d-flex align-items-center">
      <img src="assets/img/Support 1.jpg" class="img-fluid rounded shadow" alt="Support Illustration">
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
