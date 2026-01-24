/* =======================
  Report Endpoint
======================= */

const REPORT_ENDPOINT = "https://apilageai.lk/reportdata.php";
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("bugReportModal");
  const openBtn = document.getElementById("reportBugLink");
  const closeBtn = document.getElementsByClassName("close")[0];
  const form = document.getElementById("bugReportForm");
  const successMessage = document.getElementById("successMessage");

  const emailInput = document.getElementById("bug-email");
  const problemInput = document.getElementById("bug-problem");
  const screenshotInput = document.getElementById("bug-screenshot");

  const emailError = document.getElementById("emailError");
  const problemError = document.getElementById("problemError");
  const screenshotError = document.getElementById("screenshotError");

  const imagePreview = document.getElementById("bugImagePreview");
  const previewImage = document.getElementById("previewImage");
  const sendReportBtn = document.getElementById("sendReport");

  // Guard when the bug report UI is not present on the page.
  if (!form) {
    return;
  }

  /* =======================
     Modal Logic
  ======================= */

  if (openBtn) {
    openBtn.onclick = (e) => {
      e.preventDefault();
      if (modal) modal.style.display = "block";
      form.style.display = "block";
      if (successMessage) successMessage.style.display = "none";
      form.reset();
      if (imagePreview) imagePreview.style.display = "none";
      clearErrors();
    };
  }

  if (closeBtn && modal) {
    closeBtn.onclick = () => {
      modal.style.display = "none";
    };
  }

  window.onclick = (e) => {
    if (modal && e.target === modal) {
      modal.style.display = "none";
    }
  };

  /* =======================
     Screenshot Preview
  ======================= */

  if (screenshotInput) {
    screenshotInput.addEventListener("change", (e) => {
      const file = e.target.files[0];

      if (!file) {
        if (imagePreview) imagePreview.style.display = "none";
        return;
      }

      if (file.size > 10 * 1024 * 1024) {
        if (screenshotError) showError(screenshotError, "File size must be less than 10MB");
        screenshotInput.value = "";
        if (imagePreview) imagePreview.style.display = "none";
        return;
      }

      if (!file.type.match("image.*")) {
        if (screenshotError) showError(screenshotError, "Please select an image file");
        screenshotInput.value = "";
        if (imagePreview) imagePreview.style.display = "none";
        return;
      }

      const reader = new FileReader();
      reader.onload = (event) => {
        if (previewImage) previewImage.src = event.target.result;
        if (imagePreview) imagePreview.style.display = "block";
        if (screenshotError) screenshotError.style.display = "none";
      };

      reader.readAsDataURL(file);
    });
  }

  /* =======================
     Validation Helpers
  ======================= */

  function validateEmail(email) {
    if (email === "") return true;
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  function validateProblem(text) {
    return text.trim().split(/\s+/).length >= 4;
  }

  function showError(element, message) {
    element.textContent = message;
    element.style.display = "block";
  }

  function clearErrors() {
    if (emailError) emailError.style.display = "none";
    if (problemError) problemError.style.display = "none";
    if (screenshotError) screenshotError.style.display = "none";
  }

  /* =======================
     Form Submission
  ======================= */

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    e.stopPropagation();
    clearErrors();

    const email = emailInput.value;
    const problem = problemInput.value;
    const screenshot = screenshotInput.files[0];

    let valid = true;

    if (email && !validateEmail(email)) {
      showError(emailError, "Please enter a valid email address");
      valid = false;
    }

    if (!problem) {
      showError(problemError, "Please describe the problem");
      valid = false;
    } else if (!validateProblem(problem)) {
      showError(problemError, "Description must be at least 4 words");
      valid = false;
    }

    if (!valid) return;

    if (sendReportBtn) {
      sendReportBtn.disabled = true;
      sendReportBtn.textContent = "Sending...";
    }

    try {
      const formData = new FormData();
      formData.append("email", email || "");
      formData.append("problem", problem);
      if (screenshot) {
        formData.append("screenshot", screenshot);
      }

      const response = await fetch(REPORT_ENDPOINT, {
        method: "POST",
        body: formData
      });

      const text = await response.text();
      if (!text || text.trim() === "") {
        if (response.ok) {
          throw new Error("Server returned empty response.");
        }
        throw new Error("Unable to submit your report right now.");
      }

      let result;
      try {
        result = JSON.parse(text);
      } catch (parseError) {
        console.error("Non-JSON response:", text);
        throw new Error("Server returned invalid response");
      }

      if (!response.ok || !result.success) {
        throw new Error(result.message || "Unable to submit your report right now.");
      }

      form.style.display = "none";
      if (successMessage) successMessage.style.display = "block";

      setTimeout(() => {
        if (modal) modal.style.display = "none";
        if (sendReportBtn) {
          sendReportBtn.disabled = false;
          sendReportBtn.textContent = "Send Report";
        }
        form.reset();
      }, 3000);

    } catch (error) {
      console.error("Error saving bug report:", error);
      alert(error.message || "There was an error submitting your report. Please try again.");
      if (sendReportBtn) {
        sendReportBtn.disabled = false;
        sendReportBtn.textContent = "Send Report";
      }
    }
    
    return false;
  });
});
