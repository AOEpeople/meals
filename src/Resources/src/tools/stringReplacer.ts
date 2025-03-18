export default function replaceStrings(input: string, replace: string = '', ...args: string[]): string {
    let output = input;
    args.forEach((arg) => {
        output = output.replace(arg, replace);
    });
    return output;
}
