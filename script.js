document.documentElement.classList.add("js");

const yearNodes = document.querySelectorAll("[data-year]");
for (const node of yearNodes) {
  node.textContent = String(new Date().getFullYear());
}

const revealItems = document.querySelectorAll(".reveal");
if ("IntersectionObserver" in window && revealItems.length > 0) {
  const observer = new IntersectionObserver(
    (entries, observerInstance) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observerInstance.unobserve(entry.target);
        }
      }
    },
    {
      rootMargin: "0px 0px -10% 0px",
      threshold: 0.12,
    },
  );

  revealItems.forEach((item) => observer.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add("is-visible"));
}

const filterButtons = Array.from(document.querySelectorAll("[data-filter]"));
const frames = Array.from(document.querySelectorAll("[data-category]"));

function setActiveFilter(nextFilter) {
  filterButtons.forEach((button) => {
    const isActive = button.dataset.filter === nextFilter;
    button.classList.toggle("is-active", isActive);
    button.setAttribute("aria-pressed", String(isActive));
  });

  frames.forEach((frame) => {
    const matches = nextFilter === "all" || frame.dataset.category === nextFilter;
    frame.hidden = !matches;
  });
}

filterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    setActiveFilter(button.dataset.filter ?? "all");
  });
});

setActiveFilter("all");
