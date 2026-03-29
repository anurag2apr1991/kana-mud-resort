"use client";

import { useEffect } from "react";

/**
 * On browser refresh (reload), show the hero: strip #hash from URL and scroll to top.
 * In-page clicks to #sections are unchanged; opening a link with a hash is unchanged.
 */
export function ScrollLandingOnReload() {
  useEffect(() => {
    const nav = performance.getEntriesByType("navigation")[0] as
      | PerformanceNavigationTiming
      | undefined;
    if (nav?.type !== "reload") return;

    const path = `${window.location.pathname}${window.location.search}`;
    if (window.location.hash) {
      window.history.replaceState(null, "", path);
    }
    window.scrollTo(0, 0);
  }, []);

  return null;
}
