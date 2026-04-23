---
name: review
description: Run a full code review on changed files (or specified files/folders). Delegates to code-reviewer agent (sonnet). Use before any PR or when reviewing new code.
---

Delegate to the `code-reviewer` agent.

## Instructions for the agent

1. Run `git diff HEAD --name-only` to get the list of changed files since last commit.
   If the user specified files or a folder in their message, review those instead.

2. Read each changed PHP file and each changed Blade file.

3. Apply the full checklist from your agent definition against each file.

4. Output the structured findings report (CRITICAL → HIGH → MEDIUM → LOW).

5. For each CRITICAL or HIGH finding: also output the exact corrected code so it can be applied immediately.

6. End with a summary line: total findings by severity and overall verdict (APROVADO / REPROVADO).

If the user says `/review` with no arguments: review all files changed since the last commit.
If the user says `/review app/Actions/Reports/`: review only that folder.
If the user says `/review app/Livewire/Voting/VotingBoard.php`: review only that file.
