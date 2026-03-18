(function () {
  const fallbackMap = {
    html: "html",
    html_basic: "html",
    css: "css",
    css_basic: "css",
    js: "js",
    js_basic: "js",
    javascript: "js",
    javascript_basic: "js",
    php: "php",
    php_basic: "php",
    wordpress: "wordpress",
    wordpress_basic: "wordpress",
    mysql: "mysql",
    mysql_basic: "mysql",
  };

  const modernCourses = new Set([
    "react",
    "vue",
    "next",
    "ts",
    "typescript",
    "tailwind",
    "python-basic",
    "django",
    "symbols",
  ]);

  function getNavCourseKey(link) {
    try {
      const href = link.getAttribute("href") || "";
      const url = new URL(href, location.href);
      return url.searchParams.get("course");
    } catch {
      return null;
    }
  }

  function resolveActiveCourseKey(courseKey) {
    return fallbackMap[courseKey] || courseKey;
  }

  function applyCourseNavState({
    courseKey,
    basicSelector = "#basic-courses",
    modernSelector = "#modern-courses",
    basicTabSelector = "#tab-basic",
    modernTabSelector = "#tab-modern",
  }) {
    const basicCourses = document.querySelector(basicSelector);
    const modernCoursesEl = document.querySelector(modernSelector);
    const tabBasic = document.querySelector(basicTabSelector);
    const tabModern = document.querySelector(modernTabSelector);
    const navLinks = document.querySelectorAll(
      `${basicSelector} a, ${modernSelector} a`,
    );

    const switchTab = (type) => {
      if (!basicCourses || !modernCoursesEl || !tabBasic || !tabModern) return;

      if (type === "modern") {
        basicCourses.style.display = "none";
        modernCoursesEl.style.display = "block";
        tabModern.classList.add("active");
        tabBasic.classList.remove("active");
      } else {
        basicCourses.style.display = "block";
        modernCoursesEl.style.display = "none";
        tabBasic.classList.add("active");
        tabModern.classList.remove("active");
      }
    };

    navLinks.forEach((link) => {
      link.classList.add("course-btn");
      link.classList.remove("active");
    });

    let activeLink = [...navLinks].find(
      (link) => getNavCourseKey(link) === courseKey,
    );

    if (!activeLink) {
      const fallbackKey = resolveActiveCourseKey(courseKey);
      activeLink = [...navLinks].find(
        (link) => getNavCourseKey(link) === fallbackKey,
      );
    }

    if (activeLink) {
      activeLink.classList.add("active");
    }

    if (tabBasic && tabModern) {
      tabBasic.addEventListener("click", () => switchTab("basic"));
      tabModern.addEventListener("click", () => switchTab("modern"));
    }

    switchTab(modernCourses.has(courseKey) ? "modern" : "basic");
  }

  window.applyCourseNavState = applyCourseNavState;
})();
