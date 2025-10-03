// Switch between sections
function showSection(sectionId) {
  document.querySelectorAll(".dashboard-section").forEach(sec => sec.classList.add("hidden"));
  document.getElementById(sectionId).classList.remove("hidden");
}
