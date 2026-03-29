import type { Core } from '@strapi/strapi';

const config = (): Core.Config.Plugin => ({
  upload: {
    config: {
      sizeOptimization: true,
      responsiveDimensions: true,
      breakpoints: {
        xlarge: 1920,
        large: 1000,
        medium: 750,
        small: 500,
      },
    },
  },
});

export default config;
