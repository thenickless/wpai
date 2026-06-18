(function () {
  "use strict";

  /**
   * Normalize a string for comparison
   */
  function normalize(str) {
    return (str || "").toString().trim().toLowerCase();
  }

  /**
   * When Schema.org markup is active, <details> is wrapped
   * in a Question <div>. We must hide/show that wrapper instead.
   */
  function getToggleElement(detailsEl) {
    const schemaWrapper = detailsEl.closest(
      '[itemscope][itemtype="https://schema.org/Question"]'
    );
    return schemaWrapper || detailsEl;
  }

  /**
   * Category/tag groups are wrapped in <section> with .wp_ai-term-content.
   * Show the section only if at least one accordion item inside is visible.
   */
  function resetGroupedTermSections(wrapper) {
    wrapper.querySelectorAll(".wp_ai-term-content").forEach((termContent) => {
      const section = termContent.closest("section");
      if (section) {
        section.style.display = "";
      }
    });
  }

  function syncGroupedTermSections(wrapper) {
    wrapper.querySelectorAll(".wp_ai-term-content").forEach((termContent) => {
      const section = termContent.closest("section");
      if (!section) {
        return;
      }
      const detailsInGroup = termContent.querySelectorAll(
        "details.wp-ai-item"
      );
      if (!detailsInGroup.length) {
        return;
      }
      const anyVisible = Array.from(detailsInGroup).some(
        (details) => getToggleElement(details).style.display !== "none"
      );
      section.style.display = anyVisible ? "" : "none";
    });
  }

  /**
   * Initialize search for a single FAQ wrapper
   */
  function initFAQSearch(wrapper) {
    if (!wrapper || wrapper.dataset.bkFaqSearchInit === "1") return;

    const input = wrapper.querySelector(".wp-ai-search__input");
    if (!input) return;

    const detailsItems = Array.from(
      wrapper.querySelectorAll("details.wp-ai-item")
    );
    if (!detailsItems.length) return;

    const minLen = parseInt(input.getAttribute("data-minlen") || "3", 10);

    const items = detailsItems.map((details) => {
      const summary = details.querySelector("summary");
      return {
        details,
        question: normalize(summary ? summary.textContent : ""),
      };
    });

    function applyFilter(value) {
      const query = normalize(value);
      if (query.length < minLen) {
        items.forEach(({ details }) => {
          getToggleElement(details).style.display = "";
        });
        resetGroupedTermSections(wrapper);
        return;
      }
      items.forEach(({ details, question }) => {
        const match = question.includes(query);
        getToggleElement(details).style.display = match ? "" : "none";
      });
      syncGroupedTermSections(wrapper);
    }

    input.addEventListener("input", () => applyFilter(input.value));
    input.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        input.value = "";
        applyFilter("");
      }
    });

    wrapper.dataset.bkFaqSearchInit = "1";
  }

  function initAll(root = document) {
    root.querySelectorAll(".wp-ai").forEach(initFAQSearch);
  }

  initAll();

  /**
   * Observer initialization after document.body is available
   */
  function initObserver() {
    if (!document.body) {
      return setTimeout(initObserver, 50); // warten, falls body noch nicht da
    }

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (!(node instanceof HTMLElement)) continue;

          if (node.matches && node.matches(".wp-ai")) {
            initFAQSearch(node);
          }
          if (node.querySelectorAll) {
            initAll(node);
          }
        }
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  initObserver();
})();