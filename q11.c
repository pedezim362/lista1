#include <stdio.h>

int main () {

    int a, b;
    puts("Digite dois números inteiros:");
    scanf("%d %d", &a, &b);

    printf("A soma de %d e %d é %d.\n", a, b, a+b);
    printf("O resultado da multiplicação de %d e %d é %d.\n", a, b, a*b);
    printf("O resultado da subtração de %d e %d é %d.\n", a, b, a-b);
    printf("O resultado da divisão de %d por %d é %.2f.\n", a, b, (float)a/b);
    printf("E o resto da divisão de %d por %d é %d.\n", a, b, a%b);

    return 0;
}