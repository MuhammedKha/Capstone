<?php
// faq.php
include 'templates/header.php';
?>

<div class="container my-5">
  <h2 class="text-center section-title">Frequently Asked Questions</h2>
  <div class="accordion mt-4" id="faqAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
          How do I book an appointment?
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Log in as a client, go to "Book Appointment", choose your provider and a time slot.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="headingTwo">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
          Can I cancel or reschedule an appointment?
        </button>
      </h2>
      <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Yes. Visit your dashboard and manage your appointments directly.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="headingThree">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
          How can I register as a provider?
        </button>
      </h2>
      <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Click “Register”, select “Provider” and fill out your details.
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
