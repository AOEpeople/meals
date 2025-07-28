import { readFile, writeFile } from "fs/promises";
import { exit } from "process";

/**
 * @typedef {object} SarifFile
 * @property {string} $schema
 * @property {string} version
 * @property {SarifRun[]} runs
 */

/**
 * @typedef {object} SarifRun
 * @property {object} tool
 * @property {AutomationDetails} automationDetails
 * @property {object} results
 */

/**
 * @typedef {object} AutomationDetails
 * @property {string} id
 */

/**
 * @param {SarifFile} sarifFile
 */
function addAutomationDetails(sarifFile) {
  let counter = 0;
  /**
   * @type {SarifRun[]}
   */
  const runs = sarifFile.runs.map((run) => {
    return {
      ...run,
      automationDetails: {
        id: `snyk-category-${counter++}/`
      },
    };
  });

  return {
    ...sarifFile,
    runs: runs,
  };
}

async function main() {
  const fileName = process.argv[2] || "./snyk.sarif";
  console.log("Starting to add categories to snyk.sarif file!");
  try {
    const snykSarifFile = JSON.parse(await readFile(fileName, "utf-8"));
    const sarifFileWithCat = addAutomationDetails(snykSarifFile);
    await writeFile(fileName, JSON.stringify(sarifFileWithCat, null, 2));
  } catch (e) {
    console.error(e);
  }
  console.log("Done adding categories to snyk.sarif file!");
  exit(0);
}

main();
