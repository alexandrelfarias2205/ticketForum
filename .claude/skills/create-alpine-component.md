---
name: create-alpine-component
description: Create an Alpine.js component for app.js. Delegates to javascript-specialist (haiku).
---

Delegate to the `javascript-specialist` agent to create the Alpine.js component.

Pass the user's full request. The agent will produce:
1. `resources/js/components/{name}.js` — exported component function
2. Registration snippet for `app.js`
3. Blade usage example

All user-visible strings in Portuguese-BR.
