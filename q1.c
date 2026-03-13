#include <stdio.h>
#include <stdlib.h>

int main() {

    int x;
    puts("Insira um valor decimal inteiro para X: ");
    scanf("%d", &x);

    printf("O valor de X na base hexadecimal é: %X.\n", x);
    printf("O valor de X na base octal é: %o.\n", x);

    return 0;
}