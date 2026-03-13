#include <stdio.h>

int main() {

    float a;
    puts("Insira um valor numérico real para A: ");
    scanf("%f", &a);

    printf("O valor de A, com uma casa decimal, é: %.1f.\n", a);

    return 0;
}