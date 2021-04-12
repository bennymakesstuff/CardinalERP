# Finch Motor Company ERP Modifications and Modules
### This repository is the placeholder for all of Finch's modules we use as part of our Dolibarr installation.
### It also contains all the modified files we use where we can either
- Not modify core Dolibarr to suit as it makes breaking changes to the main Dolibarr Project
- OR
- We have made changes but they have not been pulled into the core Dolibarr repository.

## Usage
To upgrade Dolibarr we need to follow a handful of steps

- 1) Clone this Repository to a local machine
- 2) Clone the target version of Dolibarr to the same local machine
- 3) Merge the two repositories
- 4) Resolve any conflicts arrising from the merge (Some of these can be predicted by reading Dolibarr's Changelog)
- 5) Rename or Switch to a Branch that is the next version than what we are currently sitting on for Cardinal Dolibarr
- 6) Push this merged repository to the right version branch of Cardinal Dolibarr in GitHub
- 5) Now Cardinal Dolibarr can be pulled from the Git Repo directly to the target machine in AWS (It can also be pulled to a local machine for testing)
