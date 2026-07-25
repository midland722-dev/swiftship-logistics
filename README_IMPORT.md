Import helper for copying files from midland722-dev/sxcv into this repository.

How to use:
1. On GitHub, open the Actions tab and select the "Import sxcv" workflow.
2. Run the workflow (workflow_dispatch) — it will clone midland722-dev/sxcv and copy files into the branch merge-sxcv-into-swiftship, skipping any files that already exist in this repository (Default: skip conflicts).

Notes:
- This automation was created because copying the entire source repository directly via the API in a single operation can be error-prone for large repos. Running the workflow performs the copy inside GitHub with proper auth and avoids accidental overwrites.
- After the workflow completes there will be a commit on branch merge-sxcv-into-swiftship with the imported files (if any). You can then open a Pull Request to merge into main.
