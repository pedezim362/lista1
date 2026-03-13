#include <stdio.h>

int main() {

    int x;
    puts("Insira um valor decimal inteiro para X: ");
    scanf("%d", &x);

    printf("O triplo, o quadrado e a metade de X são, respectivamente: %d, %d e %.2f.\n", 3*x, x*x, (float)x/2);

    return 0;
}