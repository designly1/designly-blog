import { decode } from "he";

export function convertCode(code) {
    code = code.replace(/<\/?span[^>]*>/g, '');
    code = decode(code);

    return code;
}