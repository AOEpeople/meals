/**
 * Disables "scroll-behavior: smooth" for every loaded page
 */
Cypress.on("window:load", (window) => {
    const styleNode = window.document.createElement("style");
    styleNode.innerHTML = "html { scroll-behavior: auto !important; }";
    window.document.head.appendChild(styleNode);
  });

  /**
   * Ignore "ResizeObserver loop limit exceeded" error
   */
  const resizeObserverLoopErrRe = /^[^(ResizeObserver loop limit exceeded)]/;
  Cypress.on("uncaught:exception", (err) => {
    return !resizeObserverLoopErrRe.test(err.message);
  });
